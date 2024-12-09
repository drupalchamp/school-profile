<?php

namespace Drupal\schoolprofiler\Template;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing custom functionalities.
 *
 * @package Drupal\schoolprofiler\Template
 */
class TwigExtension extends AbstractExtension  {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'schoolprofiler';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('phone', [
        $this,
        'formatPhone'
      ]),
    ];
  }

  /**
   * Returns the active language.
   * https://stackoverflow.com/a/21531816
   *
   * @return string
   *   value of the active language
   */
  
  public function formatPhone($s) {
	  $rx = "/
    (1)?\D*     # optional country code
    (\d{3})?\D* # optional area code
    (\d{3})\D*  # first three
    (\d{4})     # last four
    (?:\D+|$)   # extension delimiter or EOL
    (\d*)       # optional extension
/x";
preg_match($rx, $s, $matches);
if(!isset($matches[0])) return false;

$country = $matches[1];
$area = $matches[2];
$three = $matches[3];
$four = $matches[4];
$ext = $matches[5];

$out = "$three-$four";
if(!empty($area)) $out = "$area-$out";
if(!empty($country)) $out = "+$country-$out";
if(!empty($ext)) $out .= "x$ext";

// check that no digits were truncated
// if (preg_replace('/\D/', '', $s) != preg_replace('/\D/', '', $out)) return false;
return $out;
  }
}

