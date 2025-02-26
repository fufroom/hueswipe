document.addEventListener("DOMContentLoaded", function () {
    const verbs = [
        "Steal", "Jack", "Pilfer", "Swipe", "Snatch", "Appropriate", "Claim", "Liberate",
        "Yoink", "Robin-Hood", "Requisition", "Abscond with", "Confiscate", "Heist", "Smuggle",
        "Commit chromatic crimes with"
    ];

    const targets = [
        "Pantone", "Big Color", "the Vanta Black guy", "Adobe", "Paint corporations",
        "Museum curators", "The art supply cartels", "Graphic design overlords", "The color police",
        "RGB monopolists", "The ghost of Bob Ross", "Intellectual property lawyers", 
        "Copyright trolls", "The pigment mafia", 
        "Crayola’s shadow government", "Some guy named Greg who hoards Copic markers",
        "The AI art bots", "A color theory professor with a superiority complex",
        "The ancient gods of CMYK",
        "The JPEG compression demons", "That one dude who trademarked an entire shade of blue",
        "The secret underground acrylic syndicate", 
        "Whoever decided 'web safe colors' were a thing", "The entire hex code Illuminati"
    ];

    const menaces = [
        "menace", "problem", "gremlin", "absolute goblin", "feral entity", 
        "chaotic neutral owlbear", "color bandit", "unhinged rogue", "shade snatcher",
        "walking art crime", "chromatic criminal", "underground pigment dealer",
        "rogue designer", "art industry disruptor", "hue anarchist", "design outlaw",
        "saturation warlord", "print shop's worst nightmare",
        "rogue RGB alchemist"
    ];

    const chaosPhrases = [
        "What the hell is a HEIC file?",
        "What the hell is a .webp file?",
        "Legally distinct from an art crime.",
        "How do you pronounce 'gif'? Wrong. It's 'gif'.",
        "Fake it 'till you make it. Or just always fake it."
    ];

    const subtitle = document.querySelector(".subtitle");

    function getRandomElement(arr) {
        return arr[Math.floor(Math.random() * arr.length)];
    }

    function updateSubtitle() {
        if (Math.random() < 0.2) {  // 20% chance for chaos phrase
            subtitle.textContent = getRandomElement(chaosPhrases);
        } else {
            subtitle.textContent = `${getRandomElement(verbs)} colors like an absolute ${getRandomElement(menaces)}. ${getRandomElement(targets)} can’t stop you.`;
        }
    }

    updateSubtitle(); // Set random text on load
    setInterval(updateSubtitle, 30000); // Change text every 30 seconds
});

