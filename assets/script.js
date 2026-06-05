
const rarityColors = {
    "Common": "#b0b0b0",
    "Rare": "#4a90d9",
    "Epic": "#9b59b6",
    "Legendary": "#f0a500",
};

document.addEventListener('DOMContentLoaded', function (e) {

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


    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', applyAllFilters);
    }

    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', applyAllFilters);
    }
});

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
        const source = carditem.dataset.source;
        const rarity = carditem.dataset.rarity;
        const type = carditem.dataset.type;
        confirmPurchase.innerHTML = `
            <div id="black-background">
                <div class="confirm-purchase">
                    <h1 id="confirm-title">Look details before buying?</h1> 
                    <span><h1 id="gun-name" style="color: ${color}">${gun}</h1></span>
                    <span id="confirm-text">for price</span>
                    <span id="gun-price">$${price.toLocaleString()}</span>
                    
                    <form method="POST" action="weapondetail.php" style="margin-top:20px;">

    <input type="hidden" name="source" value="${source}">
    <input type="hidden" name="gun_name" value="${gun}">
    <input type="hidden" name="type" value="${type}">
    <input type="hidden" name="rarity" value="${rarity}">
    <input type="hidden" name="price" value="${price}">
    <input type="hidden" name="color" value="${color}">

    <div class="confirm-btns">
        <button type="button" id="cancel-btn">
            Cancel
        </button>

        <button type="submit" id="confirm-btn">
            Confirm
        </button>
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
function applyAllFilters() {
    const searchInput = document.getElementById("search");
    const query = searchInput ? searchInput.value.toLowerCase().trim() : "";


    const checkedTypes = Array.from(document.querySelectorAll('.filter-type:checked'))
        .map(cb => cb.value.toLowerCase());

    const checkedRarities = Array.from(document.querySelectorAll('.filter-rarity:checked'))
        .map(cb => cb.value.toLowerCase());

    const weaponCards = document.querySelectorAll(".carditem");

    weaponCards.forEach((card) => {
        const cardGun = card.getAttribute("data-gun").toLowerCase();
        const cardType = card.getAttribute("data-type").toLowerCase();
        const cardRarity = card.getAttribute("data-rarity").toLowerCase();
        const matchesSearch = cardGun.includes(query);
        const matchesType = checkedTypes.length === 0 || checkedTypes.includes(cardType);
        const matchesRarity = checkedRarities.length === 0 || checkedRarities.includes(cardRarity);

        if (matchesSearch && matchesType && matchesRarity) {
            card.style.display = "";
        } else {
            card.style.display = "none";
        }
    });
}




