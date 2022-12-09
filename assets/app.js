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
import * as events from "events";

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



// Filter settings
new Filter(document.querySelector('.js-filter'));

// Dropdown filter button
const filterButton = document.querySelector('.filter-button');
const filterForm = document.querySelector('.filter-form');
const categories = document.getElementById('categories')
const colors = document.getElementById('colors')
const stones = document.getElementById('stones')
const categoryDropdown = document.querySelector('.category-dropdown');
const colorDropdown = document.querySelector('.color-dropdown');
const stoneDropdown = document.querySelector('.stone-dropdown');

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