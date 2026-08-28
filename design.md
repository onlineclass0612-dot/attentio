# Design System: Attention OS

## Overview
**Attention OS** adalah sistem desain profesional yang dirancang untuk aplikasi manajemen kehadiran dan SDM. Sistem ini mengutamakan kejelasan, efisiensi navigasi, dan visual yang bersih untuk mendukung produktivitas admin HR maupun karyawan di lapangan.

---

## Visual Identity
- **Brand Personality**: Profesional, Terpercaya, Modern, dan Efisien.
- **Visual Style**: Clean dengan penggunaan whitespace yang terukur, sudut komponen yang halus (rounded), dan penekanan pada fungsionalitas data.

---

## Design Tokens

### Colors
Sistem warna menggunakan palet biru sebagai warna primer untuk membangun rasa kepercayaan dan profesionalitas.

| Token | Hex Value | Usage |
| :--- | :--- | :--- |
| **Primary** | `#2563eb` | Tombol utama, brand logo, status aktif. |
| **Secondary** | `#0f172a` | Header, navigasi, teks judul besar. |
| **Surface** | `#f8f9ff` | Background halaman utama. |
| **Surface-Bright** | `#ffffff` | Background kartu (cards) dan kontainer. |
| **Border-Low** | `#e2e8f0` | Pembatas antar elemen yang halus. |
| **Success** | `#22c55e` | Status hadir, tepat waktu, disetujui. |
| **Warning** | `#f59e0b` | Status terlambat, pending. |
| **Error** | `#ef4444` | Status absen (alpha), ditolak. |

### Typography
Menggunakan font **Inter** untuk keterbacaan yang optimal pada berbagai ukuran layar.

- **Headline Large**: Bold, 32px (Desktop) / 24px (Mobile)
- **Headline Medium**: Semi-bold, 20px
- **Body Regular**: 16px, Line-height 1.5
- **Label Small**: Medium, 12px (Untuk meta-data/kategori)

### Radius & Spacing
- **Roundness**: `ROUND_EIGHT` (8px) untuk konsistensi pada tombol, kartu, dan input field.
- **Gutter**: 24px (Desktop) / 16px (Mobile).

---

## Shared Components

### 1. Top App Bar (Mobile)
- **Leading**: Foto profil pengguna (Avatar).
- **Headline**: Brand name "Attention".
- **Trailing**: Ikon notifikasi dengan indikator status.
- **Style**: Sticky top, background surface dengan border-bottom tipis.

### 2. Navigation Drawer (Desktop Sidebar)
- **Header**: Logo "Attention Admin" dan profil singkat administrator.
- **Items**: Dashboard, Employees, Attendance Logs, Leave Approvals, Settings.
- **Active State**: Background biru muda dengan teks tebal.

### 3. Bottom Navigation (Mobile)
- **Destinations**: Dashboard, Clock-in, Leave, Profile.
- **Active State**: Ikon berwarna primer dengan label yang jelas.
- **Shape**: Rounded top corners dengan bayangan (shadow) halus.

### 4. Data Cards
- **Usage**: Ringkasan KPI (Total Pegawai, Hadir, Sisa Cuti).
- **Layout**: Ikon di kiri/atas, angka besar sebagai focal point, dan label keterangan di bawahnya.

---

## Design Principles
1. **Consistency**: Elemen navigasi selalu berada di posisi yang sama untuk memudahkan memori otot pengguna.
2. **Real-time Feedback**: Penggunaan indikator status warna (hijau/kuning/merah) untuk memberikan konteks instan pada data absensi.
3. **Accessibility**: Kontras warna yang tinggi antara teks dan background untuk penggunaan di luar ruangan (mobile).
