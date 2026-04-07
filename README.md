# Filament Template PPSI

Filament Template ini adalah project template filament yang sudah dihubungkan dengan aplikasi gate yang baru. Template sudah disesuaikan dengan beberapa modifikasi mengikuti warna UK Petra.

## Update guide to v4 from v3

Pull the latest upstream

```sh
git fetch upstream
git pull upstream master
```

Do the migration base on this documentation
<https://filamentphp.com/docs/4.x/upgrade-guide>

Linux

```sh
composer require filament/upgrade:"^4.0" -W --dev

vendor/bin/filament-v4

# Run the commands output by the upgrade script, they are unique to your app
composer require filament/filament:"^4.0" -W --no-update
composer update
```

PowerShell

```sh
composer require filament/upgrade:"~4.0" -W --dev

vendor/bin/filament-v4

# Run the commands output by the upgrade script, they are unique to your app
composer require filament/filament:"~4.0" -W --no-update
composer update
```

### Hapus node_modules

`node_modules` perlu dihapus karena kemungkinan akan bentrok dengan versi tailwind yang baru. Setelah hapus bisa lakukan:

```sh
npm install
npm run build
```

## Struktur folder

- app
  - Filament
    - Clusters -[ClusterName] : Nama group cluster yang dibuat di filament
    - Pages -[GroupFiturName] : Nama group fitur yang dibuat di filament -[FiturName] : Nama fitur yang dibuat di filament
    - Resources -[GroupFiturName] : Nama group fitur yang dibuat di filament -[FiturName] : Nama fitur yang dibuat di filament
    - Widgets -[WidgetName] : Nama widget yang dibuat di filament
  - Helpers
    - [GroupHelperName] : Nama group helper yang dibuat
      - [HelperName] : Nama helper yang dibuat
  - Http
    - Controllers
      - [GroupControllerName] : Nama group controller yang dibuat (untuk API di folder API)
        - [ControllerName] : Nama controller yang dibuat
  - Models
    - [GroupModelName] : Nama group model yang dibuat
      - [ModelName] : Nama model yang dibuat
  - Policies
    - [GroupPolicyName] : Nama group policy yang dibuat
      - [PolicyName] : Nama policy yang dibuat
  - Providers
    - Filament
      - FilamentServiceProvider.php : File untuk register resource, page, widget, dll di filament
  - Traits
    - AutoCreateUpdateBy.php : Trait untuk otomatis mengisi created_by dan updated_by
- lang
  - [language_code].json : File untuk translate bahasa
- recources
  - views
    - filament
      - pages
        - [GroupFiturName]
          - [FiturName].blade.php : File view untuk halaman page di filament

## Installation

Fork Template Filament

- Pilih Button Fork
- Masukkan Project name "Project"
- Pilih namespace

Sebelum clone pastikan sudah format CF untuk git nya

```sh
git config --global core.autocrlf false
git config --global core.eol lf
```

Clone di lokal

- Buat didirektori untuk project
- Pilih project yang sudah dibuat (yang sudah di fork)
- Copy URL Clone with HTTPS

Clone with SSH:

```sh
git clone git@gitlab.petra.ac.id:ppsi/template/template-filament.git
```

Clone with HTTPS:

```sh
git clone https://gitlab.petra.ac.id/ppsi/template/template-filament.git
```

Setup configuration:

```sh
cp .env.example .env
```

Configure .env:

```sh
APP_NAME=xxx
APP_ID=xxx
APP_KODE=xxx
DB_SCHEMA=xxx
```

### Using Docker

Update PHP dependencies:

Pastikan sudah punya Docker Compose
Install depedency Laravel dan NodeJS

```sh
docker compose build
docker compose run --rm --entrypoint "" web composer install --no-interaction --prefer-dist
docker compose run --rm --entrypoint "" web npm install
```

Setelah selesai install bisa jalankan

```sh
docker compose up -d
```

### Non Docker

```sh
composer install
```

Generate APP_KEY

```sh
php artisan key:generate
```

Deploy Vite:

```sh
npm install
npm run build
```

---

## Push to GitLab Guide

Lakukan Laravel Pint

Windows:

```
.\vendor\bin\pint.bat
```

Linux/Mac:

```sh
./vendor/bin/pint
```

---

## Catatan

- Untuk Ref\Unit pakailah scopeNama yang sudah disediakan di model Unit
- default Unit adalah dari info left dan info right
- Pengecekan akses pasang di Policy dan Guards
- setiap table dan model wajib ada created_by dan updated_by dan pakai trait AutoCreateUpdateBy
- label wajib multibahasan dengan memanggil \_\_('label_name') dan default bahasa Inggris di lang/en.json
- penulisan variable menggunakan camelCase
- penulisan class menggunakan PascalCase
- penulisan function menggunakan camelCase
- penulisan constant menggunakan UPPER_SNAKE_CASE
- penulisan nama file menggunakan PascalCase sesuai nama class
- penulisan nama table menggunakan snake_case
- penulisan nama foreign key menggunakan snake_case dengan akhiran \_id
- penulisan nama primary key menggunakan snake_case dengan nama id

## Cara penggunaan

- Fork Template ini
- Tambahkan protected branch dan tag dengan wildcard "v\*" di menu Settings → Repository di tiap project gitlab supaya CI/CD berjalan untuk tag dan branch tertentu dan di production
- Kemudian git pull project
- Copy gitlab-ci.yml.example menjadi .gitlab-ci.yml dan ubah value nya sesuai arahan
- Untuk menjalankan pipeline, buat tag dengan format vx.x.x (x adalah angka). cth: v0.0.1
- Jika deployment mau langsung ke dev tanpa klik manual, ketika membuat tag tambahkan tag message `always: dev`. Jika mau langsung ke production, buat tag dengan message `always: prod`. Jika mau ke keduanya tambahkan tag message `always: dev, prod`. cth `git tag v0.0.1 -m "always: prod"` | `git tag v0.0.1 -m "always: dev"` | `git tag v0.0.1 -m "always: dev, prod"`
- Proses development untuk tidak langsung push ke branch master/main. Tetapi merge terlebih dahulu di branch dev (development). Setiap fitur besar atau perubahan yang memerlukan banyak commit. Silahkan buat branch untuk fitur tersebut, di merge ke branch dev untuk testing, dan lakukan merge ke branch master/main untuk production dan lakukan tagging untuk memasukkan ke server production.
- Pengecekan akses user dilakukan di Policy dan Guards dan daftarkan di AuthServiceProvider bagian $policies
- Sesuaikan APP_YEAR di .gitlab-ci.yml dengan tahun aplikasi selesai dibuat

## Keep up with the fork

### 1. Tambah Upstream

```bash
git remote add upstream git@gitlab.petra.ac.id:flaxeon/internal/template/template-filament.git
```

Jika menggunakan https

```bash
git remote add upstream https://gitlab.petra.ac.id/flaxeon/internal/template/template-filament.git
```

### 2. Pull upstream

```bash
git pull upstream master
```

## Pengembangan Lebih Lanjut

- pemakaian yajra untuk datatable sebagai default menampilkan index
- pemakaian select2 untuk default pemakaian select
