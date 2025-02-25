import { createColorBox } from "./colorBox.js";

document.addEventListener("DOMContentLoaded", function () {
    const imageContainer = document.getElementById("imageContainer");
    const colorResults = document.getElementById("colorResults");
    const sampleAlert = document.getElementById("clickToSampleAlert"); // Added reference to the sample message box

    function showSampleAlert() {
        const image = imageContainer.querySelector("img");
        if (image) {
            sampleAlert.classList.remove("d-none"); // Show the message if an image is present
        } else {
            sampleAlert.classList.add("d-none"); // Hide the message if no image
        }
    }

    function getPixelColor(event, image) {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");

        canvas.width = image.width;
        canvas.height = image.height;
        ctx.drawImage(image, 0, 0, image.width, image.height);

        const rect = image.getBoundingClientRect();
        const scaleX = image.width / rect.width;
        const scaleY = image.height / rect.height;

        const x = Math.floor((event.clientX - rect.left) * scaleX);
        const y = Math.floor((event.clientY - rect.top) * scaleY);

        const pixelData = ctx.getImageData(x, y, 1, 1).data;
        return { r: pixelData[0], g: pixelData[1], b: pixelData[2] };
    }

    function rgbToHex(r, g, b) {
        return `#${((1 << 24) | (r << 16) | (g << 8) | b).toString(16).slice(1).toUpperCase()}`;
    }

    function rgbToHsl(r, g, b) {
        r /= 255, g /= 255, b /= 255;
        let max = Math.max(r, g, b), min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0;
        } else {
            let d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        return {
            h: Math.round(h * 360),
            s: Math.round(s * 100),
            l: Math.round(l * 100)
        };
    }

    imageContainer.addEventListener("click", function (event) {
        const image = imageContainer.querySelector("img");
        if (!image) return;

        const rgb = getPixelColor(event, image);
        const hex = rgbToHex(rgb.r, rgb.g, rgb.b);
        const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);

        console.log(`[PICKER] Picked color: ${hex}`);

        colorResults.insertAdjacentHTML("afterbegin", createColorBox(hex, rgb, hsl));
    });

    // Observe image changes and show/hide the sample alert
    const observer = new MutationObserver(showSampleAlert);
    observer.observe(imageContainer, { childList: true });

    showSampleAlert(); // Run on page load to check if image is present
});
