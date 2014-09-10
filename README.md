Rakit CLI
=========

Simple PHP framework untuk aplikasi CLI.

## Penggunaan Dasar

```php
<?php
// [yourproject]/cli.php

// pastikan sudah meload 3 class di file-file berikut
require('src/Rakit/CLI/Command.php');
require('src/Rakit/CLI/Console.php');
require('src/Rakit/CLI/App.php');

// membuat aplikasi CLI
$cli = new Rakit\CLI\App($argv);

// mendaftarkan sebuah command
$cli->command("hello", function($console) {
    $console->writeln("Hello World!");
});

// jalankan aplikasi
$cli->run();
```

Setelah itu, cobabuka terminal atau cmd. Masuk ke direktori dimana kamu menempatkan file tersebut. Kemudian ketik perintah 
```
php cli.php hello
```
Seharusnya akan muncul sebuah pesan bertuliskan `Hello World!` 

> Nama file `cli.php` bisa diubah nama dan ekstensinya sesuka hati. Nggak usah pakai ekstensi kayak `artisan` si Laravel juga boleh kok :p

## Menggunakan Argument

Untuk menggunakan argument, gunakan `addArgument($arg_name, $is_required)` pada command. Sedangkan untuk mendapatkan nilai dari sebuah argument, gunakan perintah 

Contoh:
```php
<?php
$cli->command("hello", function($console) {
    // mendapatkan nilai argument name
    $name = $console->argument("name");
    console->write('Hello '.$name.'!');
})
->addArgument("name", true); // mendaftarkan argument name
```

Jika kamu menjalankan `php cli.php hello "John doe"`. Maka akan muncul pesan bertuliskan `Hello John doe!`.

## Menggunakan Option

Untuk menggunakan option, gunakan `addOption($opt_name, $opt_type, $reqiured = false, $default = null)` pada Command.
Sedangkan untuk mengambil nilai dari option, gunakan `$console->option($opt_name, $default = NULL)`.

Contoh:
```php
use Rakit\CLI\Command;

$cli->command("hello", function($console) {
	$name = $console->argument("name");
	$caps = $console->option("");
	$console->write("Hello ".$name);
})
->argument("name", true);
->option("caps", Command::OPT_BOOLEAN, false);
```

### List Option Type


- OPT_STRING: menerima nilai apapun
- OPT_NUMBER: hanya menerima nilai berupa numeric
- OPT_ARRAY: akan mem-parse nilai ke dalam bentuk array
- OPT_BOOLEAN: hanya menerima nilai true, false, 0, 1, yes, no, on, off. Nilai kosong (ex: `php cli.php mycmd --foo`) dianggap TRUE, sedangkan jika tidak dimasukkan (ex: `php cli.php mycmd`) akan dianggap FALSE.

## Objek Console

Objek Console adalah objek yang memungkinkan kamu berinteraksi dengan console atau terminal. Objek console akan dikirimkan pada command yang dijalankan. Ada beberapa method pada objek Console yang dapat kamu gunakan, diantaranya:

### write($message, $fg_color = NULL, $bg_color = NULL)

Fungsi write digunakan untuk menampilkan sebuah text pada Console. Untuk `fg_color` dan `bg_color` hanya tersedia pada Console yang berbasis support ASCII, jadi untuk command prompt pada windows pewarnaan tidak berpengaruh.

### writeln($message, $fg_color = NULL, $bg_color = NULL)
Fungsi writeln pada dasarnya sama dengan fungsi `write()`. Hanya saja, `writeln()` digunakan untuk menulis text pada 1 baris.

### ask($question, $default_answer = NULL)
Fungsi `ask()` digunakan untuk menampilkan sebuah pertanyaan pada console. 

Contoh:
```php
$cli->command("askname", function($console) {
    $name = $console->ask("What is your name? ");
    $console->writeln("Your name is ".$name);
});
```

### prompt()
Berbeda dengan fungsi ask, fungsi prompt digunakan untuk menampilkan input secara blank(tanpa pertanyaan) ke console.

### error($message)
Digunakan untuk menampilkan pesan error dan menghentikan aplikasi.

### table(array $columns, array $data)
Table adalah fungsi spesial yang memungkinkan kamu menampilkan table pada console. Untuk contoh penggunaan bisa kamu lihat pada `src/Rakit/CLI/App.php` 

