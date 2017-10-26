# Bastardized Script for PHP 5.6
I took https://github.com/runmybusiness/pardot and made it less helpful and
modern for the sake of PHP 5.6 compatibility.

My use case was simply that I needed to quickly remove 25k or so Prospects from
my Pardot account, and I had quickest access to a PHP 5.6 binary on my
workstation.

--------

## Download and Setup
```bash
git clone https://github.com/matthewpoer/PardotProspectRemoverPhp56.git
cd PardotProspectRemoverPhp56
composer install
```

## Example Script
```php
<?php
require_once('vendor/autoload.php');
$client = new RunMyBusiness\Pardot\Client();
$client->setAuth(
  'myemailaddress@mydomain.com',
  'mypassword',
  'myuserkey'
);
$client->authenticate();

// be sure the CSV file is just the Prospect ID column, no additional collumns,
// and does not have a header (so actually not a CSV at all, but just a list of
// Prospect IDs separated by \n)
$filename = 'to_be_deleted.csv';
$prospect_ids = file($filename);

$count = 0;
echo 'Total number of prospects: ' . count($prospect_ids) . PHP_EOL;
foreach($prospect_ids as $prospect_id) {
  $prospect_id = trim($prospect_id);
  echo 'The count is ' . $count . ' and the id is ' . $prospect_id . "\r";

  // update CRM fields to avoid sync after delete
  $response = $client->update('Prospect', $prospect_id, array(
    'crm_contact_fid' => '[[crm_ignore_prospect]]',
    'crm_lead_fid' => '[[crm_ignore_prospect]]',
    'crm_url' => '[[crm_ignore_prospect]]',
  ), array(), TRUE);
  if($response !== TRUE) {
    echo 'The count is ' . $count . ' and the id is ' . $prospect_id . PHP_EOL;
    echo 'Something amiss while running update on ID of ' . $prospect_id . PHP_EOL;
    echo 'Maybe there\'s an error message? ' . print_r($response, TRUE) . PHP_EOL;
    die();
  }

  // now do the delete
  $response = $client->delete('Prospect', $prospect_id);
  if($response !== TRUE) {
    echo 'The count is ' . $count . ' and the id is ' . $prospect_id . PHP_EOL;
    echo 'Something amiss with ID of ' . $prospect_id . PHP_EOL;
    echo 'Maybe there\'s an error message? ' . print_r($response, TRUE) . PHP_EOL;
    die();
  }

  $count++;

  if($count % 1000 == 0) {
    echo 'The count is ' . $count . ' and the id is ' . $prospect_id . PHP_EOL;
    break;
  }
}

echo 'Job complete' . PHP_EOL;
```
