<?php

namespace Svbk\WP\Helpers;

class Places {

    public static function countries(){
        return array (
        	'AF' => __('Afghanistan', 'svbk-helpers'),
        	'AL' => __('Albania', 'svbk-helpers'),
        	'DZ' => __('Algeria', 'svbk-helpers'),
        	'AS' => __('American Samoa', 'svbk-helpers'),
        	'AD' => __('Andorra', 'svbk-helpers'),
        	'AO' => __('Angola', 'svbk-helpers'),
        	'AI' => __('Anguilla', 'svbk-helpers'),
        	'AQ' => __('Antarctica', 'svbk-helpers'),
        	'AG' => __('Antigua And Barbuda', 'svbk-helpers'),
        	'AR' => __('Argentina', 'svbk-helpers'),
        	'AM' => __('Armenia', 'svbk-helpers'),
        	'AW' => __('Aruba', 'svbk-helpers'),
        	'AU' => __('Australia', 'svbk-helpers'),
        	'AT' => __('Austria', 'svbk-helpers'),
        	'AZ' => __('Azerbaijan', 'svbk-helpers'),
        	'BS' => __('Bahamas', 'svbk-helpers'),
        	'BH' => __('Bahrain', 'svbk-helpers'),
        	'BD' => __('Bangladesh', 'svbk-helpers'),
        	'BB' => __('Barbados', 'svbk-helpers'),
        	'BY' => __('Belarus', 'svbk-helpers'),
        	'BE' => __('Belgium', 'svbk-helpers'),
        	'BZ' => __('Belize', 'svbk-helpers'),
        	'BJ' => __('Benin', 'svbk-helpers'),
        	'BM' => __('Bermuda', 'svbk-helpers'),
        	'BT' => __('Bhutan', 'svbk-helpers'),
        	'BO' => __('Bolivia', 'svbk-helpers'),
        	'BA' => __('Bosnia And Herzegovina', 'svbk-helpers'),
        	'BW' => __('Botswana', 'svbk-helpers'),
        	'BV' => __('Bouvet Island', 'svbk-helpers'),
        	'BR' => __('Brazil', 'svbk-helpers'),
        	'IO' => __('British Indian Ocean Territory', 'svbk-helpers'),
        	'BN' => __('Brunei Darussalam', 'svbk-helpers'),
        	'BG' => __('Bulgaria', 'svbk-helpers'),
        	'BF' => __('Burkina Faso', 'svbk-helpers'),
        	'BI' => __('Burundi', 'svbk-helpers'),
        	'KH' => __('Cambodia', 'svbk-helpers'),
        	'CM' => __('Cameroon', 'svbk-helpers'),
        	'CA' => __('Canada', 'svbk-helpers'),
        	'CV' => __('Cape Verde', 'svbk-helpers'),
        	'KY' => __('Cayman Islands', 'svbk-helpers'),
        	'CF' => __('Central African Republic', 'svbk-helpers'),
        	'TD' => __('Chad', 'svbk-helpers'),
        	'CL' => __('Chile', 'svbk-helpers'),
        	'CN' => __('China', 'svbk-helpers'),
        	'CX' => __('Christmas Island', 'svbk-helpers'),
        	'CC' => __('Cocos (keeling) Islands', 'svbk-helpers'),
        	'CO' => __('Colombia', 'svbk-helpers'),
        	'KM' => __('Comoros', 'svbk-helpers'),
        	'CG' => __('Congo', 'svbk-helpers'),
        	'CD' => __('Congo, The Democratic Republic Of The', 'svbk-helpers'),
        	'CK' => __('Cook Islands', 'svbk-helpers'),
        	'CR' => __('Costa Rica', 'svbk-helpers'),
        	'CI' => __('Cote D\'ivoire', 'svbk-helpers'),
        	'HR' => __('Croatia', 'svbk-helpers'),
        	'CU' => __('Cuba', 'svbk-helpers'),
        	'CY' => __('Cyprus', 'svbk-helpers'),
        	'CZ' => __('Czech Republic', 'svbk-helpers'),
        	'DK' => __('Denmark', 'svbk-helpers'),
        	'DJ' => __('Djibouti', 'svbk-helpers'),
        	'DM' => __('Dominica', 'svbk-helpers'),
        	'DO' => __('Dominican Republic', 'svbk-helpers'),
        	'TP' => __('East Timor', 'svbk-helpers'),
        	'EC' => __('Ecuador', 'svbk-helpers'),
        	'EG' => __('Egypt', 'svbk-helpers'),
        	'SV' => __('El Salvador', 'svbk-helpers'),
        	'GQ' => __('Equatorial Guinea', 'svbk-helpers'),
        	'ER' => __('Eritrea', 'svbk-helpers'),
        	'EE' => __('Estonia', 'svbk-helpers'),
        	'ET' => __('Ethiopia', 'svbk-helpers'),
        	'FK' => __('Falkland Islands (malvinas)', 'svbk-helpers'),
        	'FO' => __('Faroe Islands', 'svbk-helpers'),
        	'FJ' => __('Fiji', 'svbk-helpers'),
        	'FI' => __('Finland', 'svbk-helpers'),
        	'FR' => __('France', 'svbk-helpers'),
        	'GF' => __('French Guiana', 'svbk-helpers'),
        	'PF' => __('French Polynesia', 'svbk-helpers'),
        	'TF' => __('French Southern Territories', 'svbk-helpers'),
        	'GA' => __('Gabon', 'svbk-helpers'),
        	'GM' => __('Gambia', 'svbk-helpers'),
        	'GE' => __('Georgia', 'svbk-helpers'),
        	'DE' => __('Germany', 'svbk-helpers'),
        	'GH' => __('Ghana', 'svbk-helpers'),
        	'GI' => __('Gibraltar', 'svbk-helpers'),
        	'GR' => __('Greece', 'svbk-helpers'),
        	'GL' => __('Greenland', 'svbk-helpers'),
        	'GD' => __('Grenada', 'svbk-helpers'),
        	'GP' => __('Guadeloupe', 'svbk-helpers'),
        	'GU' => __('Guam', 'svbk-helpers'),
        	'GT' => __('Guatemala', 'svbk-helpers'),
        	'GN' => __('Guinea', 'svbk-helpers'),
        	'GW' => __('Guinea-bissau', 'svbk-helpers'),
        	'GY' => __('Guyana', 'svbk-helpers'),
        	'HT' => __('Haiti', 'svbk-helpers'),
        	'HM' => __('Heard Island And Mcdonald Islands', 'svbk-helpers'),
        	'VA' => __('Holy See (vatican City State)', 'svbk-helpers'),
        	'HN' => __('Honduras', 'svbk-helpers'),
        	'HK' => __('Hong Kong', 'svbk-helpers'),
        	'HU' => __('Hungary', 'svbk-helpers'),
        	'IS' => __('Iceland', 'svbk-helpers'),
        	'IN' => __('India', 'svbk-helpers'),
        	'ID' => __('Indonesia', 'svbk-helpers'),
        	'IR' => __('Iran, Islamic Republic Of', 'svbk-helpers'),
        	'IQ' => __('Iraq', 'svbk-helpers'),
        	'IE' => __('Ireland', 'svbk-helpers'),
        	'IL' => __('Israel', 'svbk-helpers'),
        	'IT' => __('Italy', 'svbk-helpers'),
        	'JM' => __('Jamaica', 'svbk-helpers'),
        	'JP' => __('Japan', 'svbk-helpers'),
        	'JO' => __('Jordan', 'svbk-helpers'),
        	'KZ' => __('Kazakstan', 'svbk-helpers'),
        	'KE' => __('Kenya', 'svbk-helpers'),
        	'KI' => __('Kiribati', 'svbk-helpers'),
        	'KP' => __('Korea, Democratic People\'s Republic Of', 'svbk-helpers'),
        	'KR' => __('Korea, Republic Of', 'svbk-helpers'),
        	'KV' => __('Kosovo', 'svbk-helpers'),
        	'KW' => __('Kuwait', 'svbk-helpers'),
        	'KG' => __('Kyrgyzstan', 'svbk-helpers'),
        	'LA' => __('Lao People\'s Democratic Republic', 'svbk-helpers'),
        	'LV' => __('Latvia', 'svbk-helpers'),
        	'LB' => __('Lebanon', 'svbk-helpers'),
        	'LS' => __('Lesotho', 'svbk-helpers'),
        	'LR' => __('Liberia', 'svbk-helpers'),
        	'LY' => __('Libyan Arab Jamahiriya', 'svbk-helpers'),
        	'LI' => __('Liechtenstein', 'svbk-helpers'),
        	'LT' => __('Lithuania', 'svbk-helpers'),
        	'LU' => __('Luxembourg', 'svbk-helpers'),
        	'MO' => __('Macau', 'svbk-helpers'),
        	'MK' => __('Macedonia, The Former Yugoslav Republic Of', 'svbk-helpers'),
        	'MG' => __('Madagascar', 'svbk-helpers'),
        	'MW' => __('Malawi', 'svbk-helpers'),
        	'MY' => __('Malaysia', 'svbk-helpers'),
        	'MV' => __('Maldives', 'svbk-helpers'),
        	'ML' => __('Mali', 'svbk-helpers'),
        	'MT' => __('Malta', 'svbk-helpers'),
        	'MH' => __('Marshall Islands', 'svbk-helpers'),
        	'MQ' => __('Martinique', 'svbk-helpers'),
        	'MR' => __('Mauritania', 'svbk-helpers'),
        	'MU' => __('Mauritius', 'svbk-helpers'),
        	'YT' => __('Mayotte', 'svbk-helpers'),
        	'MX' => __('Mexico', 'svbk-helpers'),
        	'FM' => __('Micronesia, Federated States Of', 'svbk-helpers'),
        	'MD' => __('Moldova, Republic Of', 'svbk-helpers'),
        	'MC' => __('Monaco', 'svbk-helpers'),
        	'MN' => __('Mongolia', 'svbk-helpers'),
        	'MS' => __('Montserrat', 'svbk-helpers'),
        	'ME' => __('Montenegro', 'svbk-helpers'),
        	'MA' => __('Morocco', 'svbk-helpers'),
        	'MZ' => __('Mozambique', 'svbk-helpers'),
        	'MM' => __('Myanmar', 'svbk-helpers'),
        	'NA' => __('Namibia', 'svbk-helpers'),
        	'NR' => __('Nauru', 'svbk-helpers'),
        	'NP' => __('Nepal', 'svbk-helpers'),
        	'NL' => __('Netherlands', 'svbk-helpers'),
        	'AN' => __('Netherlands Antilles', 'svbk-helpers'),
        	'NC' => __('New Caledonia', 'svbk-helpers'),
        	'NZ' => __('New Zealand', 'svbk-helpers'),
        	'NI' => __('Nicaragua', 'svbk-helpers'),
        	'NE' => __('Niger', 'svbk-helpers'),
        	'NG' => __('Nigeria', 'svbk-helpers'),
        	'NU' => __('Niue', 'svbk-helpers'),
        	'NF' => __('Norfolk Island', 'svbk-helpers'),
        	'MP' => __('Northern Mariana Islands', 'svbk-helpers'),
        	'NO' => __('Norway', 'svbk-helpers'),
        	'OM' => __('Oman', 'svbk-helpers'),
        	'PK' => __('Pakistan', 'svbk-helpers'),
        	'PW' => __('Palau', 'svbk-helpers'),
        	'PS' => __('Palestinian Territory, Occupied', 'svbk-helpers'),
        	'PA' => __('Panama', 'svbk-helpers'),
        	'PG' => __('Papua New Guinea', 'svbk-helpers'),
        	'PY' => __('Paraguay', 'svbk-helpers'),
        	'PE' => __('Peru', 'svbk-helpers'),
        	'PH' => __('Philippines', 'svbk-helpers'),
        	'PN' => __('Pitcairn', 'svbk-helpers'),
        	'PL' => __('Poland', 'svbk-helpers'),
        	'PT' => __('Portugal', 'svbk-helpers'),
        	'PR' => __('Puerto Rico', 'svbk-helpers'),
        	'QA' => __('Qatar', 'svbk-helpers'),
        	'RE' => __('Reunion', 'svbk-helpers'),
        	'RO' => __('Romania', 'svbk-helpers'),
        	'RU' => __('Russian Federation', 'svbk-helpers'),
        	'RW' => __('Rwanda', 'svbk-helpers'),
        	'SH' => __('Saint Helena', 'svbk-helpers'),
        	'KN' => __('Saint Kitts And Nevis', 'svbk-helpers'),
        	'LC' => __('Saint Lucia', 'svbk-helpers'),
        	'PM' => __('Saint Pierre And Miquelon', 'svbk-helpers'),
        	'VC' => __('Saint Vincent And The Grenadines', 'svbk-helpers'),
        	'WS' => __('Samoa', 'svbk-helpers'),
        	'SM' => __('San Marino', 'svbk-helpers'),
        	'ST' => __('Sao Tome And Principe', 'svbk-helpers'),
        	'SA' => __('Saudi Arabia', 'svbk-helpers'),
        	'SN' => __('Senegal', 'svbk-helpers'),
        	'RS' => __('Serbia', 'svbk-helpers'),
        	'SC' => __('Seychelles', 'svbk-helpers'),
        	'SL' => __('Sierra Leone', 'svbk-helpers'),
        	'SG' => __('Singapore', 'svbk-helpers'),
        	'SK' => __('Slovakia', 'svbk-helpers'),
        	'SI' => __('Slovenia', 'svbk-helpers'),
        	'SB' => __('Solomon Islands', 'svbk-helpers'),
        	'SO' => __('Somalia', 'svbk-helpers'),
        	'ZA' => __('South Africa', 'svbk-helpers'),
        	'GS' => __('South Georgia And The South Sandwich Islands', 'svbk-helpers'),
        	'ES' => __('Spain', 'svbk-helpers'),
        	'LK' => __('Sri Lanka', 'svbk-helpers'),
        	'SD' => __('Sudan', 'svbk-helpers'),
        	'SR' => __('Suriname', 'svbk-helpers'),
        	'SJ' => __('Svalbard And Jan Mayen', 'svbk-helpers'),
        	'SZ' => __('Swaziland', 'svbk-helpers'),
        	'SE' => __('Sweden', 'svbk-helpers'),
        	'CH' => __('Switzerland', 'svbk-helpers'),
        	'SY' => __('Syrian Arab Republic', 'svbk-helpers'),
        	'TW' => __('Taiwan, Province Of China', 'svbk-helpers'),
        	'TJ' => __('Tajikistan', 'svbk-helpers'),
        	'TZ' => __('Tanzania, United Republic Of', 'svbk-helpers'),
        	'TH' => __('Thailand', 'svbk-helpers'),
        	'TG' => __('Togo', 'svbk-helpers'),
        	'TK' => __('Tokelau', 'svbk-helpers'),
        	'TO' => __('Tonga', 'svbk-helpers'),
        	'TT' => __('Trinidad And Tobago', 'svbk-helpers'),
        	'TN' => __('Tunisia', 'svbk-helpers'),
        	'TR' => __('Turkey', 'svbk-helpers'),
        	'TM' => __('Turkmenistan', 'svbk-helpers'),
        	'TC' => __('Turks And Caicos Islands', 'svbk-helpers'),
        	'TV' => __('Tuvalu', 'svbk-helpers'),
        	'UG' => __('Uganda', 'svbk-helpers'),
        	'UA' => __('Ukraine', 'svbk-helpers'),
        	'AE' => __('United Arab Emirates', 'svbk-helpers'),
        	'GB' => __('United Kingdom', 'svbk-helpers'),
        	'US' => __('United States', 'svbk-helpers'),
        	'UM' => __('United States Minor Outlying Islands', 'svbk-helpers'),
        	'UY' => __('Uruguay', 'svbk-helpers'),
        	'UZ' => __('Uzbekistan', 'svbk-helpers'),
        	'VU' => __('Vanuatu', 'svbk-helpers'),
        	'VE' => __('Venezuela', 'svbk-helpers'),
        	'VN' => __('Viet Nam', 'svbk-helpers'),
        	'VG' => __('Virgin Islands, British', 'svbk-helpers'),
        	'VI' => __('Virgin Islands, U.s.', 'svbk-helpers'),
        	'WF' => __('Wallis And Futuna', 'svbk-helpers'),
        	'EH' => __('Western Sahara', 'svbk-helpers'),
        	'YE' => __('Yemen', 'svbk-helpers'),
        	'ZM' => __('Zambia', 'svbk-helpers'),
        	'ZW' => __('Zimbabwe', 'svbk-helpers'),
        );
    }

}