document.addEventListener("DOMContentLoaded", function () {
    const loadingJunk = document.getElementById("loadingJunk");

    if (!loadingJunk) return;

    const phrases = [
        "Reticulating splines...",
        "Generating random seed...",
        "Reversing polarity...",
        "Executing infinite loop...",
        "Simulating particle physics...",
        "Decoding ancient hieroglyphs...",
        "Baking the lighting...",
        "Lighting up a Twix...",
        "Optimizing randomness...",
        "Synthesizing pure entropy...",
        "Spinning the color wheel...",
        "Sharpening pixels...",
        "Pronouncing 'gif' correctly...",
        "Converting JPEGs into jpegs...",
        "Brushing up on color theory...",
        "Dithering subpixels...",
        "Enhancing...",
        "Calibrating rods and cones...",
        "Toggling between CMYK and RGB...",
        "Scanning for aesthetically pleasing hues...",
        "Tuning vibrancy to illegal levels..."
    ];

    
    function updateLoadingText() {
        const randomPhrase = phrases[Math.floor(Math.random() * phrases.length)];
        loadingJunk.textContent = randomPhrase;

        // Random time between 1.5 and 3 seconds
        const randomInterval = Math.random() * (3000 - 1500) + 1500;
        setTimeout(updateLoadingText, randomInterval);
    }

    updateLoadingText();
});
