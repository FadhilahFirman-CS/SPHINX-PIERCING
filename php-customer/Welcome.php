<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="../css-customer/Welcome.css">
</head>
<body>

<div class="container">
    <img src="../gambar/logo-sphinx.png" class="logo zoom-logo" alt="Logo">
    <h1 id="title"></h1>
    <p id="subtitle"></p>
</div>

<script>
    // Fungsi ketik otomatis yang bisa menangani tag HTML tanpa rusak
    function typeWriter(element, tokens, speed, callback) {
        let i = 0;
        function type() {
            if (i < tokens.length) {
                element.innerHTML += tokens[i];
                i++;
                setTimeout(type, speed);
            } else if (callback) {
                callback();
            }
        }
        type();
    }

    // Pisah teks per karakter, tapi tag HTML dijaga tetap utuh
    function tokenize(text) {
        const tokens = [];
        let j = 0;
        while (j < text.length) {
            if (text[j] === '<') {
                let tag = '';
                while (j < text.length && text[j] !== '>') {
                    tag += text[j++];
                }
                tag += '>';
                j++;
                tokens.push(tag);
            } else {
                tokens.push(text[j++]);
            }
        }
        return tokens;
    }

    // Tunggu animasi logo selesai dulu sebelum mulai mengetik
    setTimeout(function() {
        typeWriter(document.getElementById('title'), tokenize('WELCOME'), 100, function() {
            setTimeout(function() {
                typeWriter(document.getElementById('subtitle'), tokenize('SELAMAT DATANG DI<br>SPHINX_PIERCING_JOGJA'), 60);
            }, 200);
        });
    }, 1200);

    // Setelah semua animasi selesai, arahkan ke dashboard customer
    setTimeout(function() {
        window.location.href = 'Dashboard.php';
    }, 5500);
</script>

</body>
</html>
