const rarityColors = {
    "Common": "#b0b0b0",
    "Rare": "#4a90d9",
    "Epic": "#9b59b6",
    "Legendary": "#f0a500",
};

document.addEventListener('DOMContentLoaded', function (e) {
    // Memastikan inisialisasi awal berjalan tanpa mencegah default global yang merusak form
    const slider = document.querySelector('.slider');

    if (slider) {
        let slideIndex = 0;
        const slides = slider.querySelectorAll('img');
        const totalSlides = slides.length;

        setInterval(() => {
            slideIndex++;
            if (slideIndex >= totalSlides) {
                slideIndex = 0;
            }

            const slideWidth = slider.clientWidth;
            slider.scrollTo({
                left: slideIndex * slideWidth,
                behavior: 'smooth'
            });
        }, 2000);
    }

    // Menghubungkan fungsi filter ke tombol Apply
    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyAllFilters);
    }

    // Menghubungkan fungsi filter ke kolom pencarian (opsional untuk live search)
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', applyAllFilters);
    }
});

// Efek Hover & Detail Modal Klik Card Item
document.querySelectorAll('.carditem').forEach(function (carditem) {
    const rarity = carditem.dataset.rarity;
    const color = rarityColors[rarity] || "#fff";

    const gun = carditem.dataset.gun;
    const price = parseInt(carditem.dataset.price);

    carditem.addEventListener('mouseenter', function () {
        carditem.style.transform = 'scale(1.1)';
        carditem.style.transition = 'transform 0.3s ease';
        carditem.style.cursor = 'pointer';
    });

    carditem.addEventListener('mouseleave', function () {
        carditem.style.transform = 'scale(1)';
        carditem.style.transition = 'transform 0.3s ease';
        carditem.style.cursor = 'default';
    });

    carditem.addEventListener('click', function () {
        const existing = document.getElementById('black-background');
        if (existing) existing.remove();

        const confirmPurchase = document.createElement('div');
        confirmPurchase.innerHTML = `
            <div id="black-background">
                <div class="confirm-purchase">
                    <h1 id="confirm-title">Look details before buying?</h1> 
                    <span><h1 id="gun-name" style="color: ${color}">${gun}</h1></span>
                    <span id="confirm-text">for price</span>
                    <span id="gun-price">$${price.toLocaleString()}</span>
                    
                    <form method="POST" action="weapondetail.php" style="margin-top: 20px;">
                        <input type="hidden" name="gun_name" value="${gun}">
                        <div class="confirm-btns">
                            <button type="button" id="cancel-btn">Cancel</button>
                            <button type="submit" id="confirm-btn">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(confirmPurchase);

        confirmPurchase.querySelector('#cancel-btn').addEventListener('click', function (e) {
            e.preventDefault();
            confirmPurchase.remove();
        });
    });
});

// ================= LOGIK BARU: UNIFIED FILTER SYSTEM =================
function applyAllFilters() {
    const searchInput = document.getElementById("search");
    const query = searchInput ? searchInput.value.toLowerCase().trim() : "";

    // Ambil semua value Weapon Type yang dicentang (Fix selektor class jadi .filter-type)
    const checkedTypes = Array.from(document.querySelectorAll('.filter-type:checked'))
        .map(cb => cb.value.toLowerCase());

    // Ambil semua value Rarity yang dicentang (Fix selektor class jadi .filter-rarity)
    const checkedRarities = Array.from(document.querySelectorAll('.filter-rarity:checked'))
        .map(cb => cb.value.toLowerCase());

    const weaponCards = document.querySelectorAll(".carditem");

    weaponCards.forEach((card) => {
        const cardGun = card.getAttribute("data-gun").toLowerCase();
        const cardType = card.getAttribute("data-type").toLowerCase();
        const cardRarity = card.getAttribute("data-rarity").toLowerCase();

        // 1. Cek kecocokan Search Keyword
        const matchesSearch = cardGun.includes(query);

        // 2. Cek kecocokan Weapon Type (Jika kosong, otomatis lolos seleksi)
        const matchesType = checkedTypes.length === 0 || checkedTypes.includes(cardType);

        // 3. Cek kecocokan Rarity (Jika kosong, otomatis lolos seleksi)
        const matchesRarity = checkedRarities.length === 0 || checkedRarities.includes(cardRarity);

        // Gabungkan seluruh kondisi (Mekanisme Logika AND antar grup)
        if (matchesSearch && matchesType && matchesRarity) {
            card.style.display = ""; // Tampilkan
        } else {
            card.style.display = "none"; // Sembunyikan
        }
    });
}