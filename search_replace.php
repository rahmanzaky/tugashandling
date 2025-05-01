<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keyword = $_POST['keyword'] ?? '';
    $filename = $_POST['filename'] ?? '';
    $operation = $_POST['operation'] ?? 'highlight';
    $output_type = $_POST['output_type'] ?? 'N';

    echo "<!DOCTYPE html><html><head><title>File Keyword Search</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f7f7f7;
        }
        h2 {
            color: #333;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type='text'], select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        pre {
            background-color: #272822;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
        }
        mark {
            background-color: yellow;
            color: black;
        }
        .notice {
            background-color: #e7f3fe;
            padding: 10px;
            border-left: 5px solid #2196F3;
            margin-top: 20px;
        }
    </style>
    </head><body>";

    echo "<h2>Hasil Pemrosesan File</h2>";

    if (!file_exists($filename)) {
        echo "<div class='notice'>❌ File <b>$filename</b> tidak ditemukan!</div></body></html>";
        exit;
    }

    $lines = file($filename);
    $new_lines = [];
    $found = false;

    echo "<pre>";
    foreach ($lines as $line) {
        if (stripos($line, $keyword) !== false) {
            $found = true;
            if ($operation == "redact") {
                $processed = preg_replace("/(" . preg_quote($keyword, '/') . ")/i", "*", $line);
                echo htmlspecialchars($processed); 
                $new_lines[] = $processed;
            } else {
              
                $safe_line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
                $highlighted = preg_replace("/(" . preg_quote($keyword, '/') . ")/i", "<mark>$1</mark>", $safe_line);
                echo $highlighted;
                $new_lines[] = strip_tags($highlighted);
            }
        } else {
            echo htmlspecialchars($line);
            $new_lines[] = $line;
        }
    }
    echo "</pre>";

    if (!$found) {
        echo "<div class='notice'>🔍 Kata kunci <b>'$keyword'</b> tidak ditemukan dalam file.</div>";
    }

    if ($operation == "redact") {
        if ($output_type == "O") {
            file_put_contents($filename, implode('', $new_lines));
            echo "<div class='notice'>✅ Perubahan disimpan ke file asli: <b>$filename</b></div>";
        } elseif ($output_type == "N") {
            $pathinfo = pathinfo($filename);
            $new_filename = $pathinfo['filename'] . "-new." . ($pathinfo['extension'] ?? "txt");
            file_put_contents($new_filename, implode('', $new_lines));
            echo "<div class='notice'>✅ Perubahan disimpan ke file baru: <b>$new_filename</b></div>";
        }
    }

    echo "</body></html>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pencarian Kata Kunci di File</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f7f7f7;
        }
        h2 {
            color: #333;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type='text'], select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>Pencarian dan Pengubahan Kata</h2>

<form method="POST">
    <label>Kata Kunci:</label>
    <input type="text" name="keyword" required>

    <label>Nama File:</label>
    <input type="text" name="filename" required>

    <label>Operasi:</label>
    <select name="operation">
        <option value="highlight">Highlight</option>
        <option value="redact">Redact (*** penutup)</option>
    </select>

    <label>Tipe Keluaran:</label>
    <select name="output_type">
        <option value="N">File Baru</option>
        <option value="O">Timpa File Lama</option>
    </select>

    <button type="submit">Proses</button>
</form>

</body>
</html>