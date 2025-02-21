document.addEventListener('DOMContentLoaded', async function () {
    const fileInput = document.getElementById('fileInput');
    const swipeButton = document.getElementById('swipeButton');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const imageContainer = document.getElementById('imageContainer');
    const colorResults = document.getElementById('colorResults');
    const executionTimeDisplay = document.getElementById('executionTime');

    const uploadURL = window.location.origin + "/upload.php";
    const processURL = window.location.origin + "/process.php";

    function hexToRgb(hex) {
        if (typeof hex !== "string" || !hex.startsWith("#") || hex.length !== 7) {
            console.error("[HEX ERROR] Invalid hex value:", hex);
            return { r: 0, g: 0, b: 0 }; // Default black to prevent errors
        }
        const bigint = parseInt(hex.slice(1), 16);
        return {
            r: (bigint >> 16) & 255,
            g: (bigint >> 8) & 255,
            b: bigint & 255
        };
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

    async function handleUpload() {
        if (!fileInput.files.length) {
            alert("No file selected. Please choose an image.");
            return;
        }

        loadingIndicator.style.display = 'block';
        colorResults.innerHTML = "";
        imageContainer.innerHTML = "";
        executionTimeDisplay.innerHTML = "";

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        try {
            console.log("[UPLOAD] Sending file to server...");
            const response = await fetch(uploadURL, { method: 'POST', body: formData });

            if (!response.ok) throw new Error(`Upload failed: ${response.status}`);

            const result = await response.json();
            if (!result.success) throw new Error(`Upload error: ${result.error}`);

            console.log("[UPLOAD SUCCESS] File uploaded:", result.file);
            imageContainer.innerHTML = `<img src="${result.file}" alt="Uploaded Image">`;

            console.log("[PROCESS] Sending file for color analysis...");
            const colorResponse = await fetch(`${processURL}?image=${encodeURIComponent(result.file)}`);

            if (!colorResponse.ok) throw new Error(`Processing failed: ${colorResponse.status}`);

            const colorData = await colorResponse.json();
            if (!colorData.success) throw new Error(`Processing error: ${colorData.error}`);

            console.log("[PROCESS SUCCESS] Color data received:", colorData);

            colorResults.innerHTML = colorData.colors.map(color => {
                if (!color.hex || typeof color.hex !== "string") {
                    console.error("[DATA ERROR] Missing hex value in color:", color);
                    return "";
                }

                const rgb = hexToRgb(color.hex);
                const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);

                return `
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="color-box">
                            <div class="color-swatch" style="background: ${color.hex}"></div>
                            <div>
                                <strong>Hex:</strong> ${color.hex}<br>
                                <strong>RGB:</strong> rgb(${rgb.r}, ${rgb.g}, ${rgb.b})<br>
                                <strong>HSL:</strong> hsl(${hsl.h}, ${hsl.s}%, ${hsl.l}%)
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            if (colorData.execution_time_ms) {
                executionTimeDisplay.innerHTML = `Processing time: ${(colorData.execution_time_ms / 1000).toFixed(2)} seconds`;
            }

        } catch (error) {
            console.error("[ERROR]", error);
            alert(`An error occurred: ${error.message}`);
        } finally {
            loadingIndicator.style.display = "none";
        }
    }

    swipeButton.addEventListener('click', handleUpload);
});
