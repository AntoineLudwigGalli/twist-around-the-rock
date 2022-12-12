/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// import jQuery
import 'jquery/dist/jquery.min';

//import FontAwesome
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.min';

// Import NoUislider
import noUiSlider from 'nouislider';
import 'nouislider/dist/nouislider.min.css';
//Import Filter
import Filter from './modules/Filter';

// NoUiSlider pour le slider des prix
const slider = document.getElementById('price-slider');

if (slider) {
    const min = document.getElementById('min');
    const max = document.getElementById('max');
    const minValue = Math.floor(parseInt(slider.dataset.min, 10) / 10) * 10;
    const maxValue = Math.ceil(parseInt(slider.dataset.max, 10) / 10) * 10;
    const range = noUiSlider.create(slider, {
        start: [min.value || minValue, max.value || maxValue],
        connect: true,
        step: 10,
        range: {
            'min': minValue,
            'max': maxValue
        }
    })

    range.on('slide', function (values, handle){
        if (handle === 0){
            min.value = Math.round(values[0])
        }

        if (handle === 1){
            max.value = Math.round(values[1])
        }
    })
    range.on('end', function (values, handle){
        min.dispatchEvent(new Event('change'));
    })
}



// Filter settings (todo à réactiver si besoin de filtre dynamique en ajax
// new Filter(document.querySelector('.js-filter'));

// Dropdown filter button
const filterButton = document.querySelector('.filter-button');
const filterForm = document.querySelector('.filter-form');
const categories = document.getElementById('categories')
const colors = document.getElementById('colors')
const stones = document.getElementById('stones')
const categoryDropdown = document.querySelector('.category-dropdown');
const colorDropdown = document.querySelector('.color-dropdown');
const stoneDropdown = document.querySelector('.stone-dropdown');

if (categories || colors || stones){

    categories.classList.add('d-none');
    colors.classList.add('d-none');
    stones.classList.add('d-none');

    filterButton.addEventListener('click', function (){
        filterForm.classList.toggle('d-none');
    });
    categoryDropdown.addEventListener('click', function (){
       categories.classList.toggle('d-none');
    });
    colorDropdown.addEventListener('click', function (){
        colors.classList.toggle('d-none');
    });
    stoneDropdown.addEventListener('click', function (){
        stones.classList.toggle('d-none');
    });
}


// Ajout des catégories, pierres et couleurs

const filterFormOptions = document.querySelectorAll('.filter-form-options');
const cancelButtons = document.querySelectorAll('.cancel');
const addButtons = document.querySelectorAll(".filter-data");
const categoryButton = document.querySelector('.filter-data.category');
const colorButton = document.querySelector('.filter-data.color');
const stoneButton = document.querySelector('.filter-data.stone');
const categoryForm = document.querySelector(".filter-form-options.category");
const colorForm = document.querySelector(".filter-form-options.color");
const stoneForm = document.querySelector(".filter-form-options.stone");

if (filterFormOptions){
    filterFormOptions.forEach(formOption =>
        formOption.classList.add('d-none')
    )
    if (categoryButton && colorButton && stoneButton && cancelButtons){
        cancelButtons.forEach(cancelButton =>
            cancelButton.addEventListener('click', function (){
                addButtons.forEach(addButtons =>
                    addButtons.classList.remove('d-none')
                )
                filterFormOptions.forEach(formOption =>
                    formOption.classList.add('d-none')
                )
            })
        )

        categoryButton.addEventListener('click', function (){
            categoryButton.classList.add('d-none');
            colorButton.classList.remove('d-none');
            stoneButton.classList.remove('d-none');
            stoneForm.classList.add('d-none');
            colorForm.classList.add('d-none');
            categoryForm.classList.toggle('d-none');
        });

        colorButton.addEventListener('click', function (){
            colorButton.classList.add('d-none');
            categoryButton.classList.remove('d-none');
            stoneButton.classList.remove('d-none');
            categoryForm.classList.add('d-none');
            stoneForm.classList.add('d-none');
            colorForm.classList.toggle('d-none');
        });

        stoneButton.addEventListener('click', function (){
            stoneButton.classList.add('d-none');
            categoryButton.classList.remove('d-none');
            colorButton.classList.remove('d-none');
            categoryForm.classList.add('d-none');
            colorForm.classList.add('d-none');
            stoneForm.classList.toggle('d-none');
        });
    }
}





