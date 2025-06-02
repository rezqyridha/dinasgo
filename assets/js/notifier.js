// notifier.js
document.addEventListener("DOMContentLoaded", () => {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get("msg");

    const notifications = {
        success: {
            icon: "success",
            title: "Berhasil",
            text: "Operasi berhasil dilakukan.",
        },
        added: {
            icon: "success",
            title: "Sukses",
            text: "Data berhasil ditambahkan.",
        },
        updated: {
            icon: "success",
            title: "Berhasil Diperbarui",
            text: "Data berhasil diperbarui.",
        },
        deleted: {
            icon: "success",
            title: "Data Terhapus",
            text: "Data berhasil dihapus.",
        },
        error: {
            icon: "error",
            title: "Gagal",
            text: "Terjadi kesalahan saat memproses.",
        },
        failed: {
            icon: "error",
            title: "Gagal",
            text: "Tidak dapat menyimpan data ke database.",
        },
        kosong: {
            icon: "warning",
            title: "Form Tidak Lengkap",
            text: "Semua field wajib diisi.",
        },
        duplicate: {
            icon: "warning",
            title: "Duplikat Data",
            text: "Data yang dimasukkan sudah ada.",
        },
        duplikat: {
            icon: "warning",
            title: "Data Duplikat",
            text: "Data sudah digunakan.",
        },
        invalid: {
            icon: "warning",
            title: "Permintaan Tidak Valid",
            text: "Data tidak ditemukan atau parameter salah.",
        },
        fk_blocked: {
            icon: "error",
            title: "Tidak Bisa Dihapus",
            text: "User terkait data lain seperti pegawai, dokumen, atau notifikasi.",
        },
        unauthorized: {
            icon: "error",
            title: "Akses Ditolak",
            text: "Anda tidak memiliki izin mengakses halaman ini.",
        },
        nochange: {
            icon: "info",
            title: "Tidak Ada Perubahan",
            text: "Data tetap sama, tidak ada yang diperbarui.",
        },
    };

    if (msg && notifications[msg]) {
        Swal.fire({
            ...notifications[msg],
            timer: 2000,
            showConfirmButton: false,
        });

        // Bersihkan parameter ?msg dari URL setelah tampil
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    }
});

// Fungsi konfirmasi hapus
function confirmDelete(url) {
    Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Data akan dihapus secara permanen.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus!",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
