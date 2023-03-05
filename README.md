
# AWS S3 Images to Webp Converter

You can convert easily that you have uploaded jpg or png images to webp format on AWS S3. In this way, you'll use less bandwidth and storage area.


## Installation

Install with composer
```bash
composer require ozden/imageconverter
```
    
## Usage/Examples

```javascript
<?php
require 'vendor/autoload.php';
set_time_limit(0);
ini_set('memory_limit', '-1');

$awsCredentials = [
    'key' => 'YOUR_AWS_KEY',
    'secret' => 'YOUR_AWS_SECRET'
];

$converter = new Ozden\Converter();
$converter->setRegion('YOUR_REGION');
$converter->setBucket('YOUR_BUCKET');
$converter->connectAws($awsCredentials);

// S3 Prefix. (If you don't want to use prefix, then leave empty first argument)
// You want to convert extensions. Array
// Webp quality. 0-100. 0: worst, 100: best
// If you set true, the script will delete old jpg/png file on your S3 after upload webp file.

$result = $converter->start('backup/2022/07/', ['jpg', 'jpeg', 'jfif', 'png', 'webp'], 70, false);
var_dump($result);
```


## License

[MIT](https://choosealicense.com/licenses/mit/)

