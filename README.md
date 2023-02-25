
# AWS S3 Images to Webp Converter

A brief description of what this project does and who it's for


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

// If you don't want to use prefix, then leave empty first argument

$result = $converter->start('backup/2022/07/', ['jpg', 'jpeg', 'png'], 'webp', 80, false);
var_dump($result);
```


## License

[MIT](https://choosealicense.com/licenses/mit/)

