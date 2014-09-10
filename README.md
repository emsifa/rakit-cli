Rakit CLI
=========

Simple PHP framework untuk aplikasi CLI.

## Untuk Apa CLI?
Dalam pengembangan sebuah aplikasi, seringkali ada pekerjaan yang kita lakukan berulang-ulang dengan pola sama. Misalkan dalam mengembangkan sebuah website yang menggunakan framework berbasis MVC, kita ingin membuat sebuah Controller dan Model dimana file-file Controller dan Model tersebut biasanya memiliki keterkaitan dengan pola yang sama. Itu cukup merepotkan kalau kita mengandalkan GUI sebuah editor dimana kita mesti mengarahkan cursor kearah direktori tersebut, klik kanan, klik new file, ketik script-script yang sama secara berulang-ulang. 

Untuk menangani hal tersebut, kamu hanya perlu membungkus pola-pola kesamaan itu menjadi sebuah blok program dan buat menjadi sebuah command di aplikasi CLI. Jadi suatu saat kamu butuh, kamu hanya perlu mengetikkan beberapa baris perintah pada terminal atau command prompt kamu untuk meminimalisir pusing karena kamu tidak perlu lagi lirik sana sini untuk mengarahkan mouse kesana, klik, arahkan mouse kesini, klik, arahkan mouse kesitu, klik lagi, dst :p

## Membuat Aplikasi CLI

Buatlah sebuah file bernama `cli.php` pada direktori project kamu, kemudian isi dengan script berikut:

```php
<?php
// [yourproject]/cli.php

// pastikan sudah meload 3 Class di file-file berikut
require('src/Rakit/CLI/Command.php');
require('src/Rakit/CLI/Console.php');
require('src/Rakit/CLI/App.php');

// membuat aplikasi CLI
$cli = new Rakit\CLI\App($argv);

// jalankan aplikasi
$cli->run();
```

Thats it!

Sekarang coba buka terminal atau cmd, masuk ke direktori file tersebut. Kemudian ketikkan perintah `php cli.php`, maka akan menampilkan sebuah table yang berisi daftar command pada aplikasi CLI kamu.
 

> File `cli.php` tersebut bisa diubah nama dan ekstensinya sesuka hati. Tidak usah pakai ekstensi seperti `artisan`nya si Laravel juga bisa kok

## Menambahkan Command
Untuk menambah sebuah command, sebelum aplikasi CLI kamu di`run()`, gunakan fungsi `command($command_name, $action)` dimana `$command_name` adalah nama perintah yang mau dijalankan dan `$action` bisa berupa Callable ataupun String dengan format `classname@method`.

Contoh mendaftarkan command `hello`

```php
$cli->command('hello', function($console) {

    $console->writeln('Hello world');

});
```
Pada script diatas, `hello` adalah nama command CLI kamu, dan `function($console){...}` adalah action yang akan dijalankan jika kamu menjalankan perintah `hello`. Sedangkan `$console` pada blok action tersebut adalah sebuah **Objek Console** yang dapat kamu gunakan untuk menampilkan atau menginput sesuatu pada terminal.

Untuk memanggil perintah `hello` tersebut, gunakan perintah ini pada terminal atau cmd kamu
```
$> php cli.php hello
```
Dimana `cli.php` adalah nama file yang berisi aplikasi CLI kamu, dan `hello` adalah nama command yang ingin dipanggil.

> Action pada sebuah command bisa berupa `function`, `array($objek, $method)`, ataupun string dengan format `Classname@methodName`. 


## Menggunakan Argument

Untuk menggunakan argument, gunakan `addArgument($arg_name, $is_required)` pada command. Sedangkan untuk mendapatkan nilai dari sebuah argument, gunakan perintah `$console->argument($arg_name)`

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
Script diatas menambahkan sebuah argument bernama `name` kedalam command `hello` dan argument tersebut harus diisi ($is_required = `true`). 


Untuk melihat hasilnya, coba jalankan perintah dibawah ini pada terminal 
```
$> php cli.php hello "John doe"
```
Maka akan muncul pesan bertuliskan `Hello John doe!`.

## Menggunakan Option

Untuk menggunakan option pada sebuah command, gunakan `addOption($opt_name, $opt_type, $reqiured = false)`. 
Sedangkan untuk mengambil nilai dari option, gunakan `$console->option($opt_name, $default = NULL)`.

Contoh:
```php
use Rakit\CLI\Command;

$cli->command("hello", function($console) {
    $name = $console->argument("name");
    $caps = $console->option("caps");

    $hello = "Hello ".$name;
    if($caps) {
        $console->write(strtoupper($hello));
    } else {
        $console->write($hello);
    }
})
->argument("name", true);
->option("caps", Command::OPT_BOOLEAN, false);
```
Script diatas menambahkan sebuah option bernama `caps` pada Command `hello` dimana option tersebut bertipe `boolean` dan tidak wajib diisi ($required = `false`).

Untuk memanggil command dengan option, tambahkan `--optname=value` pada terminal. Contoh untuk menggunakan command `hello` dengan option `caps` pada script diatas:
```
$> php cli.php hello "John Doe" --caps=true
```
Pada perintah diatas, karena option `caps` bertipe `boolean`, maka nilai yang diperbolehkan hanya nilai yang bersifat `boolean`.

Berikut adalah daftar tipe option yang tersedia:


- OPT_STRING: menerima nilai apapun
- OPT_NUMBER: hanya menerima nilai berupa numeric
- OPT_ARRAY: akan mem-parse nilai ke dalam bentuk array
- OPT_BOOLEAN: hanya menerima nilai true, false, 0, 1, yes, no, on, off. Nilai kosong (ex: `php cli.php mycmd --foo`) dianggap TRUE, sedangkan jika tidak dimasukkan (ex: `php cli.php mycmd`) akan dianggap FALSE.

## Objek Console

**Objek Console** adalah objek yang memungkinkan kamu berinteraksi dengan console atau terminal. Objek console akan dikirimkan pada command yang dijalankan. Ada beberapa method pada objek Console yang dapat kamu gunakan, diantaranya:

#### write($message, $fg_color = NULL, $bg_color = NULL)

Fungsi write digunakan untuk menampilkan sebuah text pada Console. Untuk `fg_color` dan `bg_color` hanya tersedia pada Console yang berbasis bash shell, jadi untuk command prompt pada windows pewarnaan tidak berpengaruh.

#### writeln($message, $fg_color = NULL, $bg_color = NULL)
Fungsi writeln pada dasarnya sama dengan fungsi `write()`. Hanya saja, `writeln()` digunakan untuk menulis text pada 1 baris.

#### ask($question, $default_answer = NULL)
Fungsi `ask()` digunakan untuk menampilkan sebuah pertanyaan pada console. 

Contoh:
```php
$cli->command("askname", function($console) {
    $name = $console->ask("What is your name? ");
    $console->writeln("Your name is ".$name);
});
```

#### prompt()
Berbeda dengan fungsi ask, fungsi prompt digunakan untuk menampilkan input secara blank(tanpa pertanyaan) ke console.

#### error($message)
Digunakan untuk menampilkan pesan error dan menghentikan aplikasi.

#### table(array $columns, array $data)
Table adalah fungsi spesial yang memungkinkan kamu menampilkan table pada console. Untuk contoh penggunaan bisa kamu lihat pada `src/Rakit/CLI/App.php`

#### argument($arg_name, $default = NULL)
Fungsi argument digunakan untuk mengambil sebuah argument yang telah didaftarkan pada sebuah Command. Jika argument tersebut tidak diisi(`NULL`), maka nilai pada `$default` yang akan di-return.

#### option($opt_name, $default = NULL)
Fungsi option digunakan untuk mengambil nilai pada option yang telah didaftarkan di sebuah Command. Jika option tidak diberikan, maka nilai pada `$default` yang akan di-return.
