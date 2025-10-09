# Profile Dropdown Refactor - Documentation

## 📋 Ringkasan Perubahan

Layout aplikasi telah direfactor untuk memindahkan profile button dari sidebar bawah ke navbar kanan atas, sejajar dengan tombol dark/light mode.

## ✨ Fitur Utama

### 1. **Avatar Dropdown di Navbar**

-   Avatar berbentuk lingkaran dengan ukuran **40x40px** (36x36px di mobile)
-   Posisi: **Pojok kanan atas navbar**, sejajar dengan tombol theme toggle
-   Gambar default: `/images/avatar.svg`
-   Fallback: Jika gambar tidak tersedia, tampil initials "AA" dengan background abu-abu

### 2. **Dropdown Menu**

-   **2 Opsi Menu:**
    -   **Profile** → Mengarah ke `route('profile.show')`
    -   **Logout** → POST ke `route('logout')` dengan CSRF protection
-   **Animasi:** Smooth fadeIn/fadeOut dengan transform scale
-   **Posisi:** Muncul di kanan bawah avatar dengan jarak 8px
-   **Responsif:** Menyesuaikan ukuran di layar mobile

### 3. **Sidebar Clean**

-   Sidebar footer (yang berisi profile button lama) telah **dihapus sepenuhnya**
-   Sidebar content sekarang menggunakan `flex-grow-1` untuk mengisi ruang yang tersisa
-   Navigasi tetap berfungsi normal dengan toggle collapse

## 🎨 Struktur Kode

### File yang Dimodifikasi:

1. **`resources/views/layouts/app.blade.php`**

    - Menghapus `profileDropdownOpen` dari Alpine.js data global
    - Menghapus sidebar footer sepenuhnya
    - Menambahkan profile dropdown di navbar dengan Alpine.js local state

2. **`resources/css/app.css`**

    - Menambahkan style untuk `.profile-avatar-btn`
    - Menambahkan style untuk `.profile-dropdown-menu`
    - Menambahkan animasi classes untuk smooth transition
    - Responsive adjustments untuk mobile

3. **`routes/web.php`**

    - Menambahkan route `profile.show`
    - Menambahkan route `logout` (POST)

4. **`resources/views/profile/show.blade.php`** (Baru)

    - Halaman profile placeholder

5. **`public/images/avatar.svg`** (Baru)
    - Avatar default dengan SVG icon user

## 🔧 Implementasi Detail

### Avatar Button HTML:

```blade
<button class="profile-avatar-btn" @click="profileDropdownOpen = !profileDropdownOpen">
    <img src="/images/avatar.svg" alt="User Avatar" class="avatar-image"
         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
    <div class="avatar-fallback" style="display: none;">
        <span>AA</span>
    </div>
</button>
```

### Dropdown Menu HTML:

```blade
<div class="profile-dropdown-menu"
     x-show="profileDropdownOpen"
     x-transition:enter="profile-dropdown-enter"
     x-transition:enter-start="profile-dropdown-enter-start"
     x-transition:enter-end="profile-dropdown-enter-end"
     x-transition:leave="profile-dropdown-leave"
     x-transition:leave-start="profile-dropdown-leave-start"
     x-transition:leave-end="profile-dropdown-leave-end"
     @click.away="profileDropdownOpen = false">

    <a href="{{ route('profile.show') }}" class="dropdown-item">
        <svg>...</svg>
        Profile
    </a>

    <div class="dropdown-divider"></div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="dropdown-item">
            <svg>...</svg>
            Logout
        </button>
    </form>
</div>
```

## 📱 Responsivitas

### Desktop (≥768px):

-   Avatar: 40x40px
-   Dropdown: min-width 200px
-   Posisi dropdown: right aligned dengan navbar

### Mobile (<768px):

-   Avatar: 36x36px
-   Dropdown: min-width 180px, right: -8px
-   Tetap di pojok kanan atas
-   Hamburger menu di kiri untuk toggle sidebar

## 🎯 Animasi

### Dropdown Enter:

-   Duration: 0.2s ease-out
-   From: `opacity: 0, translateY(-8px), scale(0.95)`
-   To: `opacity: 1, translateY(0), scale(1)`

### Dropdown Leave:

-   Duration: 0.15s ease-in
-   From: `opacity: 1, translateY(0), scale(1)`
-   To: `opacity: 0, translateY(-8px), scale(0.95)`

### Avatar Hover:

-   Border color berubah ke `var(--primary)`
-   Scale: 1.05

### Avatar Active:

-   Scale: 0.95

## 🌓 Dark Mode Support

Semua komponen mendukung dark mode dengan:

-   Variable CSS dinamis (`var(--card)`, `var(--border)`, dll)
-   Shadow yang menyesuaikan intensitas
-   Backdrop blur untuk efek glassmorphism di dark mode

## 🔄 Migration Notes

### Untuk Developer:

1. **Alpine.js State:** Profile dropdown sekarang menggunakan local state dengan `x-data="{ profileDropdownOpen: false }"`
2. **Click Outside:** Menggunakan `@click.away` untuk menutup dropdown
3. **Image Fallback:** Otomatis tampil initials jika gambar error
4. **CSRF Protection:** Logout form sudah include `@csrf`

### Untuk User:

1. Profile button sekarang di **kanan atas navbar**
2. Klik avatar untuk membuka menu
3. Pilih **Profile** untuk melihat pengaturan profil
4. Pilih **Logout** untuk keluar dari aplikasi

## 📝 TODO / Future Enhancements

-   [ ] Integrasi dengan authentication system (Laravel Breeze/Jetstream)
-   [ ] Upload avatar dari halaman profile
-   [ ] Tampilkan user data dinamis (nama, email dari database)
-   [ ] Tambahkan notifikasi di navbar
-   [ ] Tambahkan menu Settings di dropdown
-   [ ] Multi-language support untuk menu items

## 🐛 Troubleshooting

### Dropdown tidak muncul:

-   Pastikan Alpine.js loaded dengan benar
-   Cek console untuk JavaScript errors
-   Pastikan CSS sudah dikompilasi (`npm run build` atau `npm run dev`)

### Avatar tidak muncul:

-   Pastikan file `/public/images/avatar.svg` exists
-   Periksa permission folder `public/images`
-   Fallback akan otomatis tampil initials jika gambar error

### Animasi tidak smooth:

-   Pastikan browser support CSS transitions
-   Cek apakah ada CSS conflict dengan Bootstrap
-   Gunakan browser modern (Chrome, Firefox, Safari, Edge)

## 📚 References

-   Alpine.js Documentation: https://alpinejs.dev
-   Bootstrap 5 Dropdown: https://getbootstrap.com/docs/5.3/components/dropdowns/
-   CSS Transitions: https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Transitions

---

**Last Updated:** Oktober 9, 2025  
**Version:** 1.0.0  
**Author:** Dragon Fortune AI Team
