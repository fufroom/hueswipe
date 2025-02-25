document.addEventListener("DOMContentLoaded", function () {
    const imageFlavors = [
        "This picture slaps. Let’s see what it’s hiding.",
        "Oh cool, a picture. I sure hope you have good taste.",
        "Damn, that's a fine image. Hope the colors aren't mid.",
        "This image has seen things. Dark things. Vibrant things.",
        "I see you've brought an offering. Let’s extract its soul.",
        "Nice picture. Let’s violate its personal space.",
        "Come up with your own colors? In this economy?",
        "Ah yes, digital archaeology. Let's unearth some pigments.",
        "Fancy pixels. Hope they got that drip.",
        "You have uploaded a picture. Now we will extract its essence.",
        "This image is about to get absolutely robbed.",
        "Hope this image has the good stuff.",
        "That’s adorable. Time to mine its secrets.",
        "The image gods are watching. Don’t disappoint them.",
        "Nobody's gonna tell Pantone shit, okay?"
    ];

    const colorFlavors = [
        "Nice colors, mind if I steal them?",
        "Oh yeah, these are getting swiped.",
        "Legally, I am required to tell you these colors are public domain now.",
        "Hope you like the color palette. Because it’s mine now.",
        "Wow, nature really popped off with these hues.",
        "Mmm yes, delicious pixels. Nutritious.",
        "Nice color choices. Totally not stealing them.",
        "Holy crap, that’s some primo pigment.",
        "Oooh, the color gods have blessed us today.",
        "These colors go hard. Might have to use them myself.",
        "Hmmm, these colors are certified bangers.",
        "Your image has been distilled into its finest components.",
        "You ever just look at a color and go 'damn'?",
        "This palette is looking like an indie game title screen.",
        "These colors better be ready for their big break.",
        "I will now refer to this as MY color scheme.",
        "These colors? Absolute main character energy.",
        "This palette is what dreams are made of."
    ];

    const imageFlavorText = document.getElementById("imageFlavortext");
    const colorFlavorText = document.getElementById("colorFlavortext");

    if (imageFlavorText) {
        imageFlavorText.textContent = imageFlavors[Math.floor(Math.random() * imageFlavors.length)];
       
    } else {
        console.warn("[WARNING] imageFlavortext element not found.");
    }

    if (colorFlavorText) {
        colorFlavorText.textContent = colorFlavors[Math.floor(Math.random() * colorFlavors.length)];
        colorFlavorText.style.display = "none"; // Initially hidden, will be shown when colors are generated.
    } else {
        console.warn("[WARNING] colorFlavortext element not found.");
    }
});
