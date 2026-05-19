// c:\Users\it_room\Desktop\www\projektT2A\assets\script.js

document.addEventListener('DOMContentLoaded', () => {
    // Získáme reference na potřebné elementy
    const sliderContainer = document.querySelector('.slider-container');
    const images = document.querySelectorAll('.product-slider-img');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    // Pokud nejsou nalezeny všechny elementy nebo nejsou žádné obrázky, ukončíme skript
    if (!sliderContainer || images.length === 0 || !prevBtn || !nextBtn) {
        console.warn('Elementy pro produktový slider nebyly nalezeny nebo nejsou žádné obrázky k zobrazení.');
        return;
    }

    let currentIndex = 0; // Aktuální index zobrazeného obrázku
    const totalImages = images.length; // Celkový počet obrázků

    // Dynamicky nastavíme šířku kontejneru a obrázků podle jejich počtu
    sliderContainer.style.width = `${totalImages * 100}%`;
    images.forEach(img => img.style.width = `${100 / totalImages}%`);

    // Funkce pro zobrazení konkrétního obrázku
    function showImage(index) {
        // Zajistíme, aby se index cyklicky vracel na začátek/konec
        if (index >= totalImages) {
            currentIndex = 0;
        } else if (index < 0) {
            currentIndex = totalImages - 1;
        } else {
            currentIndex = index;
        }

        // Posuneme slider-container pomocí CSS transformace
        sliderContainer.style.transform = `translateX(-${(currentIndex * 100) / totalImages}%)`;
    }

    // Přidáme posluchače událostí na tlačítka
    prevBtn.addEventListener('click', () => showImage(currentIndex - 1));
    nextBtn.addEventListener('click', () => showImage(currentIndex + 1));

    showImage(currentIndex); // Zobrazíme první obrázek při načtení stránky
});