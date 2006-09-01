<?php 

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


function m2f_countrycodes($country = NULL)
{
	$codes = array('AB' => 'Abkhazian', 'OM' => 'Afan (Oromo)', 'AA' => 'Afar', 'AF' => 'Afrikaans', 'SQ' => 'Albanian', 'AM' => 'Amharic', 'AR' => 'Arabic', 'HY' => 'Armenian', 'AS' => 'Assamese', 'AY' => 'Aymara', 'AZ' => 'Azerbaijani', 
	'BA' => 'Bashkir', 'EU' => 'Basque', 'BN' => 'Bengali/Bangla', 'DZ' => 'Bhutani', 'BH' => 'Bihari', 'BI' => 'Bislama', 'BR' => 'Breton', 'BG' => 'Bulgarian', 'MY' => 'Burmese', 'BE' => 'Byelorussian', 
	'KM' => 'Cambodian', 'CA' => 'Catalan', 'ZH' => 'Chinese', 'CO' => 'Corsican', 'HR' => 'Croatian', 'CS' => 'Czech', 
	'DA' => 'Danish', 'NL' => 'Dutch', 
	'EN' => 'English', 'EO' => 'Esperanto', 'ET' => 'Estonian', 
	'FO' => 'Faroese', 'FJ' => 'Fiji', 'FI' => 'Finnish', 'FR' => 'French', 'FY' => 'Frisian', 
	'GL' => 'Galician', 'KA' => 'Georgian', 'DE' => 'German', 'EL' => 'Greek', 'KL' => 'Greenlandic', 'GN' => 'Guarani', 'GU' => 'Gujarati', 
	'HA' => 'Hausa', 'HE' => 'Hebrew', 'HI' => 'Hindi', 'HU' => 'Hungarian', 
	'IS' => 'Icelandic', 'ID' => 'Indonesian', 'IA' => 'Interlingua', 'IE' => 'Interlingue', 'IU' => 'Inuktitut', 'IK' => 'Inupiak', 'GA' => 'Irish', 'IT' => 'Italian', 
	'JA' => 'Japanese', 'JV' => 'Javanese', 
	'KN' => 'Kannada', 'KS' => 'Kashmiri', 'KK' => 'Kazakh', 'RW' => 'Kinyarwanda', 'KY' => 'Kirghiz', 'RN' => 'Kurundi', 'KO' => 'Korean', 'KU' => 'Kurdish', 
	'LO' => 'Laothian', 'LA' => 'Latin', 'LV' => 'Latvian/Lettish', 'LN' => 'Lingala', 'LT' => 'Lithuanian', 
	'MK' => 'Macedonian', 'MG' => 'Malagasy', 'MS' => 'Malay', 'ML' => 'Malayalam', 'MT' => 'Maltese', 'MI' => 'Maori', 'MR' => 'Marathi', 'MO' => 'Moldavian', 'MN' => 'Mongolian', 
	'NA' => 'Nauru', 'NE' => 'Nepali', 'NO' => 'Norwegian', 
	'OC' => 'Occitan', 'OR' => 'Oriya', 
	'PS' => 'Pashto/Pushto', 'FA' => 'Persian (Farsi)', 'PL' => 'Polish', 'PT' => 'Portuguese', 'PA' => 'Punjabi', 
	'QU' => 'Quechua', 
	'RM' => 'Rhaeto-romance', 'RO' => 'Romanian', 'RU' => 'Russian', 
	'SM' => 'Samoan', 'SG' => 'Sangho', 'SA' => 'Sanskrit', 'GA' => 'Scots', 'SR' => 'Serbian', 'SH' => 'Serbo-croatian', 'ST' => 'Sesotho', 'TN' => 'Setswana', 'SN' => 'Shona', 'SD' => 'Sindhi', 'SI' => 'Singhalese', 'SS' => 'Siswati', 'SK' => 'Slovak', 'SL' => 'Slovenian', 'SO' => 'Somali', 'ES' => 'Spanish', 'SU' => 'Sundanese', 'SW' => 'Swahili', 'SV' => 'Swedish', 
	'TL' => 'Tagalog', 'TG' => 'Tajik', 'TA' => 'Tamil', 'TT' => 'Tatar', 'TE' => 'Telugu', 'TH' => 'Thai', 'BO' => 'Tibetan', 'TI' => 'Tigrinya', 'TO' => 'Tonga', 'TS' => 'Tsonga', 'TR' => 'Turkish', 'TK' => 'Turkmen', 'TW' => 'Twi', 
	'UG' => 'Uigur', 'UK' => 'Ukrainian', 'UR' => 'Urdu', 'UZ' => 'Uzbek', 
	'VI' => 'Vietnamese', 'VO' => 'Volapuk', 
	'CY' => 'Welsh', 'WO' => 'Wolof', 
	'XH' => 'Xhosa', 
	'YI' => 'Yiddish', 'YO' => 'Yoruba', 
	'ZA' => 'Zhuang', 'ZU' => 'Zulu');
	
	return ($country) ? (isset($codes[$country]) ? $codes[$country] : NULL) : $codes;
} 
?>