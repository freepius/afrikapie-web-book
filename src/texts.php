<?php

/**
 * WARNING: This file is to include in bootstrap.php !
 *
 * It contains:
 *
 *   1. an array of text titles
 *
 *   2. an array of all published texts (past, present and future),
 *      indexed by publication dates
 *
 *   3. an array of really published texts (past and present only)
 *      with their publication date
 *
 *   4. an array of all activated countries
 *
 *   5. an array by country, containing its published texts by month
 */


/*******************************************************************************
 * Text titles.
 * Key = a text slug ; value = its title
 *
 * WARNING: MUST BE from oldest to newest !
 */
$app['text.titles'] =
[
    '2013-10-02' => '2 octobre 2013',
    '2013-10-03' => '3 octobre 2013',
    '2013-10-04' => '4 octobre 2013',
    '2013-10-05' => '5 octobre 2013',
    '2013-10-05-soir' => 'Soirée du 5 octobre 2013',
    '2013-10-06' => '6 octobre 2013',
    '2013-10-07' => '7 octobre 2013',
    '2013-10-08' => '8 octobre 2013',
    '2013-10-09' => '9 octobre 2013',
    '2013-10-10' => '10 octobre 2013',
    '2013-10-11' => '11 octobre 2013',
    '2013-10-12' => '12 octobre 2013',
    '2013-10-13' => 'Première prière',
    '2013-10-14' => '14 octobre 2013',
    '2013-10-16' => '16 octobre 2013',
    '2013-10-17' => '17 octobre 2013',
    '2013-10-18' => 'Songes d’hospitalité',
    '2013-10-19' => '19 octobre 2013',
    '2013-10-20' => '20 octobre 2013',
    '2013-10-21' => '21 octobre 2013',
    '2013-10-22' => '22 octobre 2013',
    '2013-10-25' => '25 octobre 2013',
    '2013-10-25-philo' => 'Philosophie du marcheur',
    '2013-10-26' => '26 octobre 2013',
];


/*******************************************************************************
 * All published texts (past, present and future).
 *
 * Key = Publication date ; value = published texts
 *
 * Key is 1 <=> static texts (always published)
 * Value is string <=> a non-publication message
 *
 * WARNING: MUST BE from newest to oldest !
 */
$app['text.published.all'] = $allPub =
[
    '2015-05-04' => ['2013-10-26'],
    '2015-05-03' => 'Jour chômé ; reprise lundi.',
    '2015-05-02' => ['2013-10-25-philo'],
    '2015-05-01' => ['2013-10-25'],
    '2015-04-30' => ['2013-10-22'],
    '2015-04-29' => ['2013-10-21'],
    '2015-04-28' => ['2013-10-20'],
    '2015-04-27' => ['2013-10-19'],
    '2015-04-26' => 'Jour chômé ; reprise lundi.',
    '2015-04-25' => ['2013-10-18'],
    '2015-04-24' => ['2013-10-17'],
    '2015-04-23' => ['2013-10-16'],
    '2015-04-22' => ['2013-10-14'],
    '2015-04-21' => ['2013-10-13'],
    '2015-04-20' => ['2013-10-12'],
    '2015-04-19' => ['2013-10-11'],
    '2015-04-18' => ['2013-10-10'],
    '2015-04-17' => ['2013-10-09'],
    '2015-04-16' => ['2013-10-08'],
    '2015-04-15' => ['2013-10-07', '2013-10-06'],
    '2015-04-14' => ['2013-10-05-soir', '2013-10-05'],
    '2015-04-13' => ['2013-10-04'],
    '2015-04-12' => ['2013-10-03', '2013-10-02'],
    1            => ['catalogue-aquarelles', 'catalogue-photos', 'nous-soutenir'],
];


/*******************************************************************************
 * Really published texts (past and present only) with their publication date.
 *
 * Key = a text slug ; value = publication date
 */
$reallyPub = [];
$today     = date('Y-m-d');

while (list($pubDate, $texts) = each($allPub))
{
    if ($pubDate > $today || is_string($texts)) { continue; }

    $reallyPub += array_fill_keys($texts, $pubDate);
}

$app['text.published.really'] = $reallyPub;


/*******************************************************************************
 * All activated countries
 */
$app['countries'] = ['Maroc', 'Mauritanie'];


/*******************************************************************************
 *  All published texts by country-month
 */
$allEndDate = [
    'Maroc'      => '2014-01-01',
    'Mauritanie' => '2014-03-04',
    'Sénégal'    => '2014-05-09',
    'Mali'       => '2014-06-13',
];

$allMonths = [
    'Maroc'      => ['10', '11', '12', '01'],
    'Mauritanie' => ['01', '02', '03'],
    'Sénégal'    => ['03', '04', '05'],
    'Mali'       => ['05', '06'],
];

// Create and init. one array by country
foreach ($allMonths as $country => $months)
{
    $countriesTexts[$country] = array_fill_keys($months, []);
}

// First country: Morocco
list($country, $endDate) = each($allEndDate);

$countryTexts =& $countriesTexts['Maroc'];

// Traverse all known texts
foreach ($app['text.titles'] as $slug => $title)
{
    // If text is unpublished => go on next
    if (! array_key_exists($slug, $reallyPub)) { continue; }

    // If end of the current country
    if ($slug > $endDate)
    {
        // Try to go on next
        if (list($country, $endDate) = each($allEndDate))
        {
            $countryTexts =& $countriesTexts[$country];
        }

        // If no country anymore => finish the traversing!
        else { break; }
    }

    // Compute the text "short title"
    list(, $month, $day) = explode('-', $slug);

    $short = strtok($title, ' ');

    if (! is_numeric($short)) { $short = substr($title, 0, 12).'...'; }

    // Store the current text in country-month
    $countryTexts[$month][$slug] = [$title, $short];
}

// Store the "country arrays" in $app
foreach ($countriesTexts as $country => $texts)
{
    $app[$country] = $texts;
}
