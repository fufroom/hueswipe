# README for Hue Swipe

Welcome to **[Hue Swipe](https://hueswipe.click)* – the simple, no-nonsense website where you can upload an image and swipe its best colors. It's totally legal, and I promise. Just an easy way to grab colors from any image you like and put them to good use.

Built with PHP because, well... **shit works**. I know, I know..

## Features
- **Easy Upload**: Just upload an image, and It’ll handle the rest.
- **Best Colors**: I’ve set up an algorithm to extract the most interesting colors from your image.
- **Click and Sample**: You can also click on the image to manually sample any color. Take control of your palette.
- **Open Source**: You can dive into the code, make it your own, or even break it if that’s your thing.

## How It Works
1. **Upload your image**.
2. **Get your colors**. The top 20 colors are extracted from the image.
3. **Click and sample**. Pick your favorite colors directly from the image.
4. **Download and use**. Take your palette and use it wherever you like. Simple as that.

## Tech Stack
- **PHP 7.x**:  It works, But, uh... I really need to update this server soon.
- **GD Library**: I’m using GD for image manipulation. It helps me extract pixels and turn them into something usable.
- **cURL**: Used for making HTTP requests.
- **IP-API**: To get the location info based on the uploader’s IP. Helps me track that.

## Credits

- **Developer & Maintainer**: [fufroom](https://fufroom.art)
- **Support the project**: [Ko-fi](https://ko-fi.com/fufroom)
- **License**: MIT License – Feel free to use it, modify it, and share it. But keep in mind: "Hue Swipe" is copyrighted, that's my name. Everything else is up for grabs.
- **Logo**: Uses icons from [Noun Project](https://thenounproject.com/), including:
    - **Paint** by Paisley (CC BY 3.0)
    - **Theft** by Vectorstall (CC BY 3.0)

## License

This project is open-source and available under the [MIT License](LICENSE). The only thing that’s copyrighted is the name **"Hue Swipe"** – that's mine. All the code? Free for you to use.

---

## Meta Description

Hue Swipe lets you upload an image and grab the best colors from it. Upload, extract, and get inspired!

---

### Getting Involved

Want to improve Hue Swipe? Open a pull request. I’m always down for ideas, so feel free to add features, fix bugs, or make the code better. Just don’t break it, please. 

---

I do log IP addresses to blacklist folks who try to upload anything creepy. It's mostly for generating some cool stats about how many people from around the world might use it, maybe? Someday? Just trying to track that for fun, not for anything shady. Don’t worry, no personal stuff is logged.

If you like what I’m doing with **Hue Swipe**, consider supporting me on [Ko-fi](https://ko-fi.com/fufroom). Your support helps keep the project alive!

---

### Credits for libraries and tools:
- **PHP** – [PHP Manual](https://www.php.net/manual/en/)
- **GD Library** – [GD Documentation](https://www.php.net/manual/en/book.image.php)
- **cURL** – [cURL Documentation](https://www.php.net/manual/en/book.curl.php)
- **IP-API** – [IP-API](https://ip-api.com/)

Enjoy the ride! And remember, **Hue Swipe** is copyright 2025, all rights reserved except for the open-source code.
