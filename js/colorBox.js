export function createColorBox(colorHex, rgb, hsl) {
    return `
        <div class="col-lg-4 col-md-6 col-12">
            <div class="color-box">
                <div class="color-swatch" style="background: ${colorHex}"></div>
                <div>
                    <strong>Hex:</strong> ${colorHex}<br>
                    <strong>RGB:</strong> rgb(${rgb.r}, ${rgb.g}, ${rgb.b})<br>
                    <strong>HSL:</strong> hsl(${hsl.h}, ${hsl.s}%, ${hsl.l}%)
                </div>
            </div>
        </div>
    `;
}
