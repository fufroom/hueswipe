document.addEventListener('DOMContentLoaded', async function () {
    const fileInput = document.getElementById('fileInput');
    const swipeButton = document.getElementById('swipeButton');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const colorFlavortext = document.getElementById('colorFlavortext');
    const imageContainer = document.getElementById('imageContainer');
    const colorResults = document.getElementById('colorResults');
   
    const uploadURL = window.location.origin + "/upload.php";
    const processURL = window.location.origin + "/process.php";

    async function handleUpload() {
        if (!fileInput.files.length) return;
    
        loadingIndicator.style.display = 'block';
        colorResults.innerHTML = "";
      
        imageContainer.innerHTML = "";
    
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
    
        try {
            const response = await fetch(uploadURL, { method: 'POST', body: formData });
            if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
    
            const responseText = await response.text();
            console.log("[UPLOAD RAW RESPONSE]", responseText);
    
            try {
                const result = JSON.parse(responseText);
                if (!result.success) {
                    throw new Error(result.error);
                }
    
                imageContainer.innerHTML = `<img src="${result.file}" alt="Uploaded Image">`;
    
                const colorResponse = await fetch(`${processURL}?image=${encodeURIComponent(result.file)}`);
                if (!colorResponse.ok) throw new Error(`HTTP Error: ${colorResponse.status}`);
    
                const colorText = await colorResponse.text();
                console.log("[COLOR RESPONSE]", colorText);
                const colorData = JSON.parse(colorText);
    
                if (colorData.success) {
                    colorResults.innerHTML = colorData.colors.map(color => `
                        <div class="color-box">
                            <div class="color-swatch" style="background: ${color.hex}"></div>
                            <div>
                                <strong>Hex:</strong> ${color.hex}<br>
                                <strong>RGB:</strong> rgb(${color.rgb.r}, ${color.rgb.g}, ${color.rgb.b})<br>
                                <strong>HSL:</strong> hsl(${color.hsl.h}, ${color.hsl.s}%, ${color.hsl.l}%)
                            </div>
                        </div>
                    `).join('');
                    if (colorFlavortext) colorFlavortext.style.display = "block"; // Avoid null error
                }
            } catch (jsonError) {
                console.error("[JSON PARSE ERROR]", responseText);
                alert("Unexpected server response. Check console for details.");
            }
        } catch (error) {
            console.error("[ERROR]", error);
            alert("An error occurred: " + error.message);
        } finally {
            loadingIndicator.style.display = "none";
        }
    }
    
    // Attach event listener - Upload only when clicking the Swipe button
    swipeButton.addEventListener('click', handleUpload);
});
