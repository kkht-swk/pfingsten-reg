/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// Get references to the input fields
const input1 = document.getElementById('team_info_spielervegan');
const input2 = document.getElementById('team_info_spielerfleisch');
const input3 = document.getElementById('team_info_betreuervegan');
const input4 = document.getElementById('team_info_betreuerfleisch');

// Get reference to the element where the sum will be displayed
const sum0Display = document.getElementById('m0');
const sum1Display = document.getElementById('m1');
const sum2Display = document.getElementById('m2');
const sumSpielerDisplay = document.getElementById('s0');
const sumBetreuerDisplay = document.getElementById('s1');
// const sum3Display = document.getElementById('m3');

// Function to calculate the sum
function calculateSum() {
    // Parse the values of input fields as numbers
    const value1 = parseFloat(input1.value) || 0;
    const value2 = parseFloat(input2.value) || 0;
    const value3 = parseFloat(input3.value) || 0;
    const value4 = parseFloat(input4.value) || 0;

    // Calculate the sum
    const cntSpieler = value1 + value2;
    const cntBetreuer = value3 + value4;
    const cost = (cntSpieler + cntBetreuer) * 90;

    // Display the sum
    sumSpielerDisplay.textContent = cntSpieler;
    sumBetreuerDisplay.textContent = cntBetreuer;

    sum0Display.textContent = cost;
    sum1Display.textContent = cost;
    sum2Display.textContent = cost;
    // sum3Display.textContent = sum;
}

// Add event listeners to each input field
input1.addEventListener('input', calculateSum);
input2.addEventListener('input', calculateSum);
input3.addEventListener('input', calculateSum);
input4.addEventListener('input', calculateSum);
