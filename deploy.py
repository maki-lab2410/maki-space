#!/usr/bin/env python3
"""
Maki Space - Smart FTP Auto Deployer for cPanel
Created for Maki Space project.
"""

import os
import sys
import time
import json
import ftplib
import ssl
import hashlib
import urllib.request
import urllib.error

PROJECT_ROOT = os.path.dirname(os.path.abspath(__file__))
CACHE_FILE = os.path.join(PROJECT_ROOT, '.deploy-cache.json')
ENV_DEPLOY_FILE = os.path.join(PROJECT_ROOT, '.env.deploy')
ENV_FILE = os.path.join(PROJECT_ROOT, '.env')

# Warna Terminal
GREEN = "\033[92m"
YELLOW = "\033[93m"
BLUE = "\033[94m"
CYAN = "\033[96m"
RED = "\033[91m"
BOLD = "\033[1m"
RESET = "\033[0m"

# Folder & File yang diabaikan saat scanning
IGNORE_DIRS = {
    '.git', '.github', 'vendor', 'node_modules', 'storage', 
    'tests', '.idea', '.vscode', '.system_generated', '__pycache__'
}

IGNORE_EXTENSIONS = {
    '.zip', '.tar', '.gz', '.log', '.sql', '.key', '.DS_Store'
}

IGNORE_FILES = {
    '.env', '.env.deploy', '.deploy-cache.json', 'deploy.py', 'deploy.sh',
    'Thumbs.db', '.phpunit.result.cache'
}

def load_config():
    """Membaca konfigurasi FTP dari .env.deploy atau .env"""
    config = {
        'FTP_HOST': '',
        'FTP_PORT': '21',
        'FTP_USER': '',
        'FTP_PASS': '',
        'FTP_REMOTE_ROOT': '',
        'FTP_SSL': 'false',
        'CACHE_CLEAR_URL': ''
    }

    # Cek file .env.deploy terlebih dahulu, lalu .env
    for filepath in [ENV_DEPLOY_FILE, ENV_FILE]:
        if os.path.exists(filepath):
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                for line in f:
                    line = line.strip()
                    if not line or line.startswith('#') or '=' not in line:
                        continue
                    k, v = line.split('=', 1)
                    k = k.strip()
                    v = v.strip().strip('"').strip("'")
                    if k in config:
                        config[k] = v

    return config

def file_md5(filepath):
    """Menghitung MD5 hash dari file lokal"""
    hasher = hashlib.md5()
    try:
        with open(filepath, 'rb') as f:
            for chunk in iter(lambda: f.read(65536), b""):
                hasher.update(chunk)
        return hasher.hexdigest()
    except Exception:
        return None

def scan_files(target_paths=None):
    """Memindai file-file project yang perlu di-deploy"""
    files_to_check = []

    if target_paths:
        for p in target_paths:
            full_p = os.path.abspath(p)
            if os.path.isfile(full_p):
                files_to_check.append(full_p)
            elif os.path.isdir(full_p):
                for root, dirs, files in os.walk(full_p):
                    dirs[:] = [d for d in dirs if d not in IGNORE_DIRS]
                    for f in files:
                        if f not in IGNORE_FILES and not any(f.endswith(ext) for ext in IGNORE_EXTENSIONS):
                            files_to_check.append(os.path.join(root, f))
    else:
        # Default scan folders
        scan_targets = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'public']
        for target in scan_targets:
            target_dir = os.path.join(PROJECT_ROOT, target)
            if not os.path.exists(target_dir):
                continue
            for root, dirs, files in os.walk(target_dir):
                # Lewati direktori yang diabaikan
                dirs[:] = [d for d in dirs if d not in IGNORE_DIRS]
                for f in files:
                    if f in IGNORE_FILES or any(f.endswith(ext) for ext in IGNORE_EXTENSIONS):
                        continue
                    # Skip compile cache
                    if 'bootstrap/cache' in root.replace('\\', '/') and f != '.gitignore':
                        continue
                    files_to_check.append(os.path.join(root, f))

        # Root files
        for rf in ['artisan', 'composer.json']:
            full_rf = os.path.join(PROJECT_ROOT, rf)
            if os.path.exists(full_rf):
                files_to_check.append(full_rf)

    return sorted(files_to_check)

def load_cache():
    if os.path.exists(CACHE_FILE):
        try:
            with open(CACHE_FILE, 'r', encoding='utf-8') as f:
                return json.load(f)
        except Exception:
            return {}
    return {}

def save_cache(cache):
    try:
        with open(CACHE_FILE, 'w', encoding='utf-8') as f:
            json.dump(cache, f, indent=2)
    except Exception:
        pass

def connect_ftp(config):
    """Membuka koneksi FTP / FTPS ke cPanel"""
    host = config['FTP_HOST']
    port = int(config['FTP_PORT'] or 21)
    user = config['FTP_USER']
    passwd = config['FTP_PASS']
    use_ssl = config.get('FTP_SSL', 'false').lower() in ('true', '1', 'yes')

    print(f"{CYAN}📡 Menghubungkan ke FTP {host}:{port} ({user})...{RESET}")

    ftp = None
    if use_ssl:
        try:
            ftp = ftplib.FTP_TLS()
            ftp.connect(host, port, timeout=30)
            ftp.login(user, passwd)
            ftp.prot_p()
            print(f"{GREEN}🔒 Terhubung menggunakan FTPS (TLS Terenkripsi)!{RESET}")
        except Exception as e:
            print(f"{YELLOW}⚠️  FTPS gagal ({e}), mencoba FTP standar...{RESET}")
            use_ssl = False

    if not ftp:
        ftp = ftplib.FTP()
        ftp.connect(host, port, timeout=30)
        ftp.login(user, passwd)
        print(f"{GREEN}✅ Terhubung menggunakan FTP standar!{RESET}")

    ftp.set_pasv(True)
    return ftp

def cd_or_mkdir(ftp, folder):
    """Pindah ke direktori, atau buat direktori jika belum ada"""
    try:
        ftp.cwd(folder)
    except Exception:
        try:
            ftp.mkd(folder)
        except Exception:
            pass
        ftp.cwd(folder)

def ensure_and_cwd(ftp, base_root, target_dir_rel):
    """Navigasi ke direktori tujuan di server secara aman"""
    try:
        ftp.cwd("/")
    except Exception:
        pass

    if base_root:
        for p in [x for x in base_root.replace('\\', '/').strip('/').split('/') if x]:
            cd_or_mkdir(ftp, p)

    if target_dir_rel:
        for p in [x for x in target_dir_rel.replace('\\', '/').strip('/').split('/') if x]:
            cd_or_mkdir(ftp, p)

def clear_remote_cache(url):
    """Memanggil endpoint clear-cache di browser setelah upload"""
    if not url:
        return
    print(f"\n{CYAN}🧹 Membersihkan cache Laravel di server...{RESET}")
    print(f"   URL: {url}")
    try:
        req = urllib.request.Request(
            url, 
            headers={'User-Agent': 'MakiSpace-AutoDeploy/1.0'}
        )
        with urllib.request.urlopen(req, timeout=15) as res:
            if res.status == 200:
                print(f"{GREEN}✅ Cache view & config Laravel berhasil dibersihkan!{RESET}")
            else:
                print(f"{YELLOW}ℹ️  Cache clear response: {res.status}{RESET}")
    except Exception as e:
        print(f"{YELLOW}⚠️  Gagal memanggil clear-cache otomatis ({e}). Anda bisa buka manual di browser.{RESET}")

def main():
    print(f"\n{BOLD}{BLUE}===================================================={RESET}")
    print(f"{BOLD}{BLUE}      🚀 MAKI SPACE - AUTO DEPLOY FTP (cPanel)     {RESET}")
    print(f"{BOLD}{BLUE}===================================================={RESET}\n")

    config = load_config()

    # Validasi apakah konfigurasi sudah diisi
    if not config['FTP_HOST'] or not config['FTP_USER'] or not config['FTP_PASS']:
        print(f"{YELLOW}⚠️  Konfigurasi FTP belum lengkap!{RESET}")
        print(f"Silakan buka atau buat file {BOLD}.env.deploy{RESET} di root project, isi data:")
        print(f"\n{CYAN}FTP_HOST={config['FTP_HOST'] or 'ftp.your-domain.com'}")
        print(f"FTP_PORT=21")
        print(f"FTP_USER={config['FTP_USER'] or 'your_username'}")
        print(f"FTP_PASS=password_cpanel_anda")
        print(f"FTP_REMOTE_ROOT=maki-space-core{RESET}\n")
        
        # Buat template jika belum ada
        if not os.path.exists(ENV_DEPLOY_FILE):
            with open(ENV_DEPLOY_FILE, 'w') as f:
                f.write(f"FTP_HOST=ftp.your-domain.com\nFTP_PORT=21\nFTP_USER=your_username\nFTP_PASS=\nFTP_REMOTE_ROOT=maki-space-core\nFTP_SSL=false\nCACHE_CLEAR_URL=https://your-domain.com/clear-cache\n")
            print(f"{GREEN}Template file .env.deploy sudah dibuat otomatis. Cukup isi FTP_PASS di file tersebut lalu jalankan kembali!{RESET}")
        return

    # Opsi argumen
    args = sys.argv[1:]
    force_all = '--all' in args
    args = [a for a in args if not a.startswith('--')]

    # Pindai file lokal
    print(f"{CYAN}🔍 Memindai file-file project...{RESET}")
    local_files = scan_files(args if args else None)
    cache = load_cache()

    changed_files = []
    new_cache = dict(cache)

    for fpath in local_files:
        rel = os.path.relpath(fpath, PROJECT_ROOT).replace('\\', '/')
        h = file_md5(fpath)
        if force_all or cache.get(rel) != h:
            changed_files.append((fpath, rel, h))

    if not changed_files:
        print(f"\n{GREEN}✨ Semua file lokal sudah sama persis dengan server. Tidak ada perubahan yang perlu di-upload!{RESET}")
        return

    print(f"\n{BOLD}📦 Ditemukan {len(changed_files)} file yang berubah / baru:{RESET}")
    for _, rel, _ in changed_files[:15]:
        print(f"   {CYAN}↑{RESET} {rel}")
    if len(changed_files) > 15:
        print(f"   ... dan {len(changed_files) - 15} file lainnya.")

    # Hubungkan ke FTP
    try:
        ftp = connect_ftp(config)
    except Exception as e:
        print(f"\n{RED}❌ Gagal terhubung ke FTP: {e}{RESET}")
        print(f"{YELLOW}Tips: Pastikan FTP_HOST, FTP_USER, dan FTP_PASS di .env.deploy sudah sesuai dengan akun cPanel Anda.{RESET}")
        return

    # Mulai proses upload
    root_display = config.get('FTP_REMOTE_ROOT', '') or '/'
    print(f"\n{BOLD}🚀 Memulai pengunggahan file ke cPanel ({root_display})...{RESET}")
    success_count = 0
    fail_count = 0

    base_root = config.get('FTP_REMOTE_ROOT', '').strip()

    for idx, (fpath, rel, h) in enumerate(changed_files, start=1):
        rel_dir = os.path.dirname(rel)
        filename = os.path.basename(rel)

        try:
            ensure_and_cwd(ftp, base_root, rel_dir)
            with open(fpath, 'rb') as f:
                ftp.storbinary(f"STOR {filename}", f)
            new_cache[rel] = h
            success_count += 1
            print(f" [{idx}/{len(changed_files)}] {GREEN}✔{RESET} {rel}")
        except Exception as e:
            fail_count += 1
            print(f" [{idx}/{len(changed_files)}] {RED}✘ Gagal:{RESET} {rel} ({e})")

    # Tutup FTP
    try:
        ftp.quit()
    except Exception:
        pass

    save_cache(new_cache)

    print(f"\n{BOLD}===================================================={RESET}")
    if fail_count == 0:
        print(f"{GREEN}🎉 Sukses! Sebanyak {success_count} file berhasil di-deploy ke server!{RESET}")
    else:
        print(f"{YELLOW}⚠️  Selesai: {success_count} berhasil, {fail_count} gagal.{RESET}")

    # Bersihkan cache
    clear_remote_cache(config.get('CACHE_CLEAR_URL', ''))

    print(f"{BOLD}{BLUE}===================================================={RESET}\n")

if __name__ == '__main__':
    main()
