# Dokumentasi Revisi Sistem TBM (Time-Based Maintenance)

## Overview

Sistem Penjadwalan Preventive Maintenance (TBM) telah direvisi untuk mengakomodasi data riil dari pabrik dengan penambahan metrik **Jenis Kerusakan** dan perhitungan **Availability (Ketersediaan Mesin)**.

## Perubahan Arsitektur Data

### 1. Tabel Baru: `jenis_kerusakan`

Tabel master untuk menyimpan data jenis/tipe kerusakan mesin.

**Struktur:**
```
- id (bigint, primary key)
- nama_kerusakan (string)
- deskripsi (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

**Data Master (Seeded):**
1. Kerusakan Mekanik
2. Kerusakan Elektrik
3. Kerusakan Elektronik
4. Kerusakan Hidrolik
5. Kerusakan Pneumatik
6. Kerusakan Struktural
7. Kerusakan Sistem Pendingin
8. Kerusakan Sistem Pelumas
9. Keausan Normal (Wear)
10. Kerusakan Akibat Overload

### 2. Update Tabel: `maintenance_histories`

**Kolom Baru yang Ditambahkan:**

| Kolom                     | Tipe          | Deskripsi                            |
| ------------------------- | ------------- | ------------------------------------ |
| `waktu_operasi_jam`       | decimal(10,2) | Input waktu operasi dalam jam (T)    |
| `waktu_perbaikan_jam`     | decimal(10,2) | Input waktu perbaikan dalam jam (Tr) |
| `jumlah_kerusakan`        | integer       | Input jumlah kerusakan (N)           |
| `jenis_kerusakan_id`      | foreign key   | Relasi ke tabel jenis_kerusakan      |
| `availability_percentage` | decimal(5,2)  | Hasil kalkulasi Availability (%)     |

**Kolom yang Dipertahankan (Backward Compatibility):**
- `total_operating_time`
- `failure_count`
- `total_repair_time`
- `mtbf`
- `mttr`
- `tpm_interval`

## Formula Bisnis (Business Logic)

### 1. MTBF (Mean Time Between Failure)
```
MTBF = T / N
```
- **T** = Total Waktu Operasi (jam)
- **N** = Jumlah Kerusakan

**Contoh:**
- T = 2400 jam
- N = 4
- MTBF = 2400 / 4 = **600 jam**

**Interpretasi:** Rata-rata mesin beroperasi 600 jam sebelum terjadi kegagalan.

### 2. MTTR (Mean Time To Repair)
```
MTTR = Tr / N
```
- **Tr** = Total Waktu Perbaikan (jam)
- **N** = Jumlah Kerusakan

**Contoh:**
- Tr = 40 jam
- N = 4
- MTTR = 40 / 4 = **10 jam**

**Interpretasi:** Rata-rata waktu yang dibutuhkan untuk memperbaiki mesin adalah 10 jam.

### 3. Availability (Ketersediaan Mesin) ⭐ BARU
```
Availability = (MTBF / (MTBF + MTTR)) × 100%
```

**Contoh:**
- MTBF = 600 jam
- MTTR = 10 jam
- Availability = (600 / (600 + 10)) × 100% = **98.36%**

**Interpretasi:** Mesin tersedia untuk beroperasi 98.36% dari total waktu. Semakin tinggi nilai Availability, semakin baik performa mesin.

### 4. Interval Tpm (Interval Preventive Maintenance)
```
Interval Tpm = MTBF × k
```
- **k** = Faktor Keamanan (default: 0.7)
- **MTBF** = Mean Time Between Failure

**Contoh:**
- MTBF = 600 jam
- k = 0.7
- Interval Tpm = 600 × 0.7 = **420 jam**

**Interpretasi:** Maintenance preventif harus dilakukan setiap 420 jam operasi.

## Models & Relationships

### 1. Model `JenisKerusakan`
```php
namespace App\Models;

class JenisKerusakan extends Model
{
    protected $table = 'jenis_kerusakan';
    
    protected $fillable = [
        'nama_kerusakan',
        'deskripsi',
    ];
    
    // Relationships
    public function maintenanceHistories(): HasMany
    {
        return $this->hasMany(MaintenanceHistory::class, 'jenis_kerusakan_id');
    }
}
```

### 2. Update Model `MaintenanceHistory`

**Fillable yang Ditambahkan:**
- `waktu_operasi_jam`
- `waktu_perbaikan_jam`
- `jumlah_kerusakan`
- `jenis_kerusakan_id`
- `availability_percentage`

**Relationship Baru:**
```php
public function jenisKerusakan(): BelongsTo
{
    return $this->belongsTo(JenisKerusakan::class, 'jenis_kerusakan_id');
}
```

## Service Layer: `TbmCalculatorService`

### Method Baru: `calculateAvailability()`

```php
/**
 * Hitung Availability (Ketersediaan Mesin).
 *
 * @param float $mtbf Nilai MTBF dalam jam
 * @param float $mttr Nilai MTTR dalam jam
 * @return float Nilai Availability dalam persentase (0-100)
 */
public function calculateAvailability(float $mtbf, float $mttr): float
{
    $total = $mtbf + $mttr;
    
    if ($total <= 0) {
        throw new \InvalidArgumentException(
            'Total MTBF + MTTR harus lebih besar dari 0 untuk menghitung Availability.'
        );
    }

    return round(($mtbf / $total) * 100, 2);
}
```

### Update Method: `calculateAll()`

Return value sekarang include **availability**:

```php
return [
    'mtbf' => $mtbf,
    'mttr' => $mttr,
    'availability' => $availability,  // ⭐ BARU
    'tpm_interval' => $tpmInterval,
    'next_schedule_date' => $nextScheduleDate,
];
```

## Controller: `MaintenanceController`

### 1. Update `storeHistory()` Method

**Validasi Request yang Ditambahkan:**
```php
'waktu_operasi_jam' => 'required|numeric|min:0.01',
'waktu_perbaikan_jam' => 'required|numeric|min:0',
'jumlah_kerusakan' => 'required|integer|min:1',
'jenis_kerusakan_id' => 'nullable|exists:jenis_kerusakan,id',
```

**Data yang Disimpan:**
```php
MaintenanceHistory::create([
    'machine_id' => $machine->id,
    'reported_by' => auth()->id(),
    'waktu_operasi_jam' => $validated['waktu_operasi_jam'],
    'waktu_perbaikan_jam' => $validated['waktu_perbaikan_jam'],
    'jumlah_kerusakan' => $validated['jumlah_kerusakan'],
    'jenis_kerusakan_id' => $validated['jenis_kerusakan_id'],
    'mtbf' => $result['mtbf'],
    'mttr' => $result['mttr'],
    'availability_percentage' => $result['availability'],  // ⭐ BARU
    'tpm_interval' => $result['tpm_interval'],
    // ... kolom lainnya
]);
```

### 2. Method Baru: Export & Rekap Data

#### a. `reportIndex()` - Halaman Rekap
Menampilkan rekapitulasi lengkap dengan statistik agregat:
- Average MTBF
- Average MTTR
- Average Availability
- Total Records

#### b. `exportData()` - Export Data ke JSON/Excel/CSV
Export data dengan filter:
- Filter berdasarkan periode (`date_from`, `date_to`)
- Filter berdasarkan mesin (`machine_id`)
- Filter berdasarkan jenis kerusakan (`jenis_kerusakan_id`)

**Response Format:**
```json
{
    "success": true,
    "data": [
        {
            "Kode Mesin": "MCH-001",
            "Nama Mesin": "Injection Molding 001",
            "Lokasi": "Lantai 1 - Line A",
            "Jenis Kerusakan": "Kerusakan Mekanik",
            "Periode Awal": "01/01/2026",
            "Periode Akhir": "31/03/2026",
            "Waktu Operasi (jam)": 2400.00,
            "Waktu Perbaikan (jam)": 40.00,
            "Jumlah Kerusakan": 4,
            "MTBF (jam)": 600.00,
            "MTTR (jam)": 10.00,
            "Availability (%)": 98.36,
            "Interval Tpm (jam)": 420.00,
            "Dilaporkan Oleh": "Admin User",
            "Catatan": "..."
        }
    ],
    "total_records": 1
}
```

#### c. `machineSummary()` - Statistik Per Mesin
Mendapatkan ringkasan performa mesin berdasarkan historis:
- Total records
- Average/Min/Max MTBF
- Average/Min/Max MTTR
- Average/Min/Max Availability
- Latest record info

**Response Format:**
```json
{
    "success": true,
    "data": {
        "machine": {
            "code": "MCH-001",
            "name": "Injection Molding 001",
            "type": "Injection Molding",
            "location": "Lantai 1 - Line A"
        },
        "statistics": {
            "total_records": 10,
            "avg_mtbf": 580.50,
            "min_mtbf": 450.00,
            "max_mtbf": 720.00,
            "avg_mttr": 12.30,
            "min_mttr": 8.50,
            "max_mttr": 18.00,
            "avg_availability": 97.95,
            "min_availability": 96.50,
            "max_availability": 98.80
        },
        "latest_record": {
            "period": "01/04/2026 - 30/06/2026",
            "mtbf": 620.00,
            "mttr": 11.00,
            "availability": 98.26
        }
    }
}
```

## File-file yang Dibuat/Dimodifikasi

### Files Dibuat:
1. ✅ `database/migrations/2026_07_04_042527_create_jenis_kerusakan_table.php`
2. ✅ `database/migrations/2026_07_04_042540_add_availability_and_jenis_kerusakan_to_maintenance_histories_table.php`
3. ✅ `app/Models/JenisKerusakan.php`
4. ✅ `database/seeders/JenisKerusakanSeeder.php`

### Files Dimodifikasi:
1. ✅ `app/Models/MaintenanceHistory.php` - Tambah relationship & kolom baru
2. ✅ `app/Services/TbmCalculatorService.php` - Tambah method calculateAvailability()
3. ✅ `app/Http/Controllers/MaintenanceController.php` - Update storeHistory() & tambah method export
4. ✅ `database/seeders/DatabaseSeeder.php` - Tambah JenisKerusakanSeeder

## Cara Penggunaan

### 1. Menjalankan Migration
```bash
php artisan migrate
```

### 2. Menjalankan Seeder
```bash
php artisan db:seed --class=JenisKerusakanSeeder
# atau
php artisan db:seed  # untuk semua seeder
```

### 3. Input Data Historis Baru (Via Controller)

**Request Parameters:**
- `machine_id` (required) - ID mesin
- `waktu_operasi_jam` (required) - Waktu operasi dalam jam
- `waktu_perbaikan_jam` (required) - Waktu perbaikan dalam jam
- `jumlah_kerusakan` (required) - Jumlah kerusakan
- `jenis_kerusakan_id` (optional) - ID jenis kerusakan
- `period_start` (required) - Tanggal awal periode
- `period_end` (required) - Tanggal akhir periode
- `notes` (optional) - Catatan tambahan

**Response:**
Sistem akan otomatis menghitung:
- MTBF
- MTTR
- Availability (%)
- Interval Tpm
- Tanggal jadwal maintenance berikutnya

### 4. Export Data
```
GET /api/maintenance/export?date_from=2026-01-01&date_to=2026-12-31&machine_id=1
```

### 5. Mendapatkan Ringkasan Per Mesin
```
GET /api/maintenance/machine-summary/{machine_id}
```

## Backward Compatibility

Sistem masih mendukung kolom lama:
- `total_operating_time` (alias untuk `waktu_operasi_jam`)
- `failure_count` (alias untuk `jumlah_kerusakan`)
- `total_repair_time` (alias untuk `waktu_perbaikan_jam`)

Sehingga data lama tetap bisa diakses dan sistem tidak break.

## Best Practices

1. **Selalu isi `jenis_kerusakan_id`** untuk kategorisasi yang lebih baik
2. **Pastikan data `waktu_operasi_jam` dan `waktu_perbaikan_jam` akurat** untuk kalkulasi yang tepat
3. **Monitor nilai Availability** - nilai di bawah 95% perlu perhatian khusus
4. **Gunakan export data** untuk analisis dan pelaporan rutin
5. **Analisis `machineSummary`** secara berkala untuk memantau tren performa mesin

## Testing

Untuk testing manual:
1. Buat data historis baru via form
2. Verifikasi kalkulasi MTBF, MTTR, Availability
3. Cek apakah jadwal maintenance ter-generate otomatis
4. Test filter dan export data
5. Verifikasi statistik per mesin

## Catatan Penting

- ⚠️ **Availability hanya dihitung jika MTBF dan MTTR > 0**
- ⚠️ **Jenis Kerusakan bersifat optional** tapi sangat direkomendasikan untuk diisi
- ⚠️ **Export data dalam format JSON** - untuk implementasi Excel/CSV, gunakan library seperti `maatwebsite/excel`

## Next Steps (Opsional)

1. Implementasi Excel Export dengan library `maatwebsite/excel`
2. Buat view/blade file untuk halaman rekap data
3. Implementasi chart/graph untuk visualisasi Availability trend
4. Tambah notifikasi email untuk Availability di bawah threshold tertentu
5. Implementasi API endpoint untuk mobile app

---

**Dokumentasi dibuat:** 4 Juli 2026  
**Framework:** Laravel 13.0  
**PHP Version:** 8.3  
**Database:** MySQL (via SQLite untuk development)
