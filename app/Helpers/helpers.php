<?php

use App\Facades\Setting;
use App\Models\RateConversion;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        if (!Auth::check()) return false;

        $user = Auth::user();
        foreach ($user->groups as $group) {
            if ($group->permissions->contains('permission_name', $permission)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('getModels')) {
    function getModels(): Collection
    {
        $models = collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $class = sprintf(
                    '\%s%s',
                    Container::getInstance()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
                );

                return $class;
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        !$reflection->isAbstract();
                }

                return $valid;
            });

        return $models->values();
    }
}

if (!function_exists('generatePassword')) {
    function generatePassword($length = 0, $strength = 0)
    {
        $password = '';
        $chars = '';
        if ($length === 0 && $strength === 0) { //set length and strenth if specified in default settings and strength isn't numeric-only
            $length = is_numeric(Setting::getSetting('users', 'password_length', 'numeric')) ? Setting::getSetting('users', 'password_length', 'numeric') : 20;
            $strength = is_numeric(Setting::getSetting('users', 'password_strength', 'numeric')) ? Setting::getSetting('users', 'password_strength', 'numeric') : 4;
        }
        if ($strength >= 1) {
            $chars .= "0123456789";
        }
        if ($strength >= 2) {
            $chars .= "abcdefghijkmnopqrstuvwxyz";
        }
        if ($strength >= 3) {
            $chars .= "ABCDEFGHIJKLMNPQRSTUVWXYZ";
        }
        if ($strength >= 4) {
            $chars .= "!^$%*?.()";
        }
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}

if (!function_exists('getAccountCode')) {
    function getAccountCode(): ?string
    {
        $accountCode = Setting::getSetting('domain', 'accountcode', 'text');
        if (!empty($accountCode)) {
            if ($accountCode === 'none') {
                $accountCode = null;
            }
        } else {
            $accountCode = Session::get('domain_name');
        }
        return $accountCode;
    }
}

if (!function_exists('findInDirectory')) {
    function findInDirectory($dir, $recursive)
    {
        $files = [];

        $tree = glob(rtrim($dir, '/') . '/*');

        if (is_array($tree)) {
            foreach ($tree as $file) {
                if (is_dir($file) && $recursive) {
                    $files = array_merge($files, findInDirectory($file, $recursive));
                } elseif (is_file($file)) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}

if (!function_exists('getSounds')) {
    function getSounds($language = null, $dialect = null, $voice = null, $rate = null): array
    {
        $array = [];

        $language = $language ?? config('sounds.default_language');
        $dialect  = $dialect  ?? config('sounds.default_dialect');
        $voice    = $voice    ?? config('sounds.default_voice');
        $rate     = $rate     ?? config('sounds.default_rate');

        $baseDir = rtrim(config('sounds.path'), '/');
        $dir = "{$baseDir}/{$language}/{$dialect}/{$voice}";

        if (!is_dir($dir)) {
            return [];
        }

        $files = findInDirectory("{$dir}/*/{$rate}", true);

        if (!empty($files)) {
            foreach ($files as $file) {
                $file = substr($file, strlen($dir) + 1);
                $file = str_replace("/{$rate}", '', $file);
                $array[] = $file;
            }
        }

        return $array;
    }
}

if (!function_exists('is_mac')) {
    function is_mac($str)
    {
        return (preg_match('/([a-fA-F0-9]{2}[:|\-]?){6}/', $str) == 1) ? true : false;
    }
}

if (!function_exists('format_mac')) {
    function format_mac($str, $delim = '-', $case = 'lower')
    {
        if (is_mac($str)) {
            $str = join($delim, str_split($str, 2));
            $str = ($case == 'upper') ? strtoupper($str) : strtolower($str);
        }
        return $str;
    }
}

if (!function_exists('array_find')) {
    function array_find(array $array, callable $callback): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return null;
    }
}

if (!function_exists('currency_select')) {
    function currency_select($currency = '', $p100 = 0, $name = 'currency')
    {

        $billingCurrency = Setting::getSetting('billing', 'currency', 'text');

        if (strlen(trim($currency)) == 0) {
            $currency = (strlen($billingCurrency) ? $billingCurrency : 'USD');
        }

        $options = config('currencies');

        if ($p100) {
            $options[] = '%';
        }

        echo "<select class='form-select' name=\"$name\" id=\"$name\">";

        foreach ($options as $code) {
            $selected = $currency === $code ? ' selected="selected"' : '';

            echo "<option value=\"$code\"$selected>$code</option>";
        }

        echo '</select>';
    }
}

if (!function_exists('github_raw_url')) {
    function github_raw_url(string $url)
    {
        // from https://github.com/CoolPBX/templates/blob/main/provision/aastra/480i/aastra.cfg
        // to https://raw.githubusercontent.com/CoolPBX/templates/refs/heads/main/provision/aastra/480i/aastra.cfg
        // var rawLink = githubLink.replace("github.com", "raw.githubusercontent.com").replace("/blob/", "/");
        $u = str_replace('github.com', 'raw.githubusercontent.com', $url);
        $u = str_repeat('/blob/', '/', $u);
        return $u;
    }
}

if (!function_exists('number_series')) {
    function number_series($number)
    {
        $ret = [];
        for ($i = strlen($number); $i > 0; $i--) {
            $ret[] = substr($number, 0, $i);
        }
        return $ret;
    }
}

if (!function_exists('currency_convert_rate')) {
    function currency_convert_rate($to = 'USD', $from = 'USD', $debug = false)
    {
        $currency_ttl = Setting::getSetting('billing', 'currency_database_cache_ttl', 'numeric') ?? 0;

        $rateConversion = RateConversion::where("from_iso4217", $from)
            ->where("to_iso4217", $to)
            ->when($currency_ttl >= 0, function ($query) use ($currency_ttl) {
                $query->where('rate_epoch', '>=', (int)$currency_ttl);
            })
            ->orderBy("rate_epoch", "desc")
            ->limit(1)
            ->first();

        return $rateConversion->rate ?? 1;
    }
}

if (!function_exists('currency_convert')) {
    function currency_convert($money, $to = 'USD', $from = 'USD')
    {
        return (float) $money * currency_convert_rate($to, $from);
    }
}

if (!function_exists('getPaymentGatewayConfig')) {
    function getPaymentGatewayConfig($paymentGateway)
    {
        $paymentGateways = config('paymentgateways');

        return $paymentGateways[$paymentGateway];
    }
}

if (!function_exists('format_string')) {
    function format_string($format, $data)
    {
        if (empty($format)) {
            return $data;
        }

        $x = 0;
        $result = '';

        $format_count = substr_count($format, 'x') + substr_count($format, 'X');
        $format_count += substr_count($format, 'R') + substr_count($format, 'r');

        if ($format_count == strlen($data)) {
            $format_length = strlen($format);

            for ($i = 0; $i < $format_length; $i++) {
                $char = substr($format, $i, 1);
                $char_lower = strtolower($char);

                if ($char_lower === 'x') {
                    $result .= substr($data, $x, 1);
                    $x++;
                } elseif ($char_lower === 'r') {
                    $x++;
                } else {
                    $result .= $char;
                }
            }
        }

        return !empty($result) ? $result : $data;
    }
}


if (!function_exists('format_phone')) {
    function format_phone($phone_number)
    {
        if (is_numeric(trim($phone_number ?? '', ' +'))) {
            $phoneFormats = session('format.phone');

            if (!empty($phoneFormats) && is_array($phoneFormats)) {
                $phone_number = trim($phone_number, ' +');

                foreach ($phoneFormats as $format) {
                    $format_count = substr_count($format, 'x');
                    $format_count += substr_count($format, 'R');
                    $format_count += substr_count($format, 'r');

                    if ($format_count == strlen($phone_number)) {
                        $phone_number = format_string($format, $phone_number);
                        break;
                    }
                }
            }
        }

        return $phone_number;
    }
}

if (!function_exists('is_windows')) {
    function is_windows(): bool
    {
        return stripos(PHP_OS, 'WIN') === 0;
    }
}

if (!function_exists('correct_path')) {
	function correct_path($p) {
		if (is_windows()) {
			return str_replace('/', '\\', $p);
		}
		return $p;
	}
}

//define function gs_cmd
if (!function_exists('gs_cmd')) {
	function gs_cmd($args) {
		if (is_windows()) {
			return 'gswin32c '.$args;
		}
		return 'gs '.$args;
	}
}

//define function fax_split dtmf
if (!function_exists('fax_split_dtmf')) {
	function fax_split_dtmf(&$fax_number, &$fax_dtmf){
		$tmp = array();
		$fax_dtmf = '';
		if (preg_match('/^\s*(.*?)\s*\((.*)\)\s*$/', $fax_number, $tmp)){
			$fax_number = $tmp[1];
			$fax_dtmf = $tmp[2];
		}
	}
}

if (!function_exists('format_string')) {
    function format_string($format, $data) {
        //nothing to do so return
        if(empty($format))
            return $data;

        //preset values
        $x=0;
        $tmp = '';

        //count the characters
        $format_count = substr_count($format, 'x');
        $format_count = $format_count + substr_count($format, 'R');
        $format_count = $format_count + substr_count($format, 'r');

        //format the string if it matches
        if ($format_count == strlen($data)) {
            for ($i = 0; $i <= strlen($format); $i++) {
                $tmp_format = strtolower(substr($format, $i, 1));
                if ($tmp_format == 'x') {
                    $tmp .= substr($data, $x, 1);
                    $x++;
                }
                elseif ($tmp_format == 'r') {
                    $x++;
                }
                else {
                    $tmp .= $tmp_format;
                }
            }
        }
        if (empty($tmp)) {
            return $data;
        }
        else {
            return $tmp;
        }
    }
}

if (!function_exists('format_phone')) {
    function format_phone($phone_number) {
        if (is_numeric(trim($phone_number ?? '', ' +'))) {
            $formatPhone = Setting::getSetting("format", "phone");
            if (!empty($formatPhone)) {
                $phone_number = trim($phone_number, ' +');
                foreach ($formatPhone as &$format) {
                    $format_count = substr_count($format, 'x');
                    $format_count = $format_count + substr_count($format, 'R');
                    $format_count = $format_count + substr_count($format, 'r');
                    if ($format_count == strlen($phone_number)) {
                        //format the number
                        $phone_number = format_string($format, $phone_number);
                    }
                }
            }
        }
        return $phone_number;
    }
}

if (!function_exists('escape')) {
    function escape($string) {
        if (is_string($string)) {
            return htmlentities($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        elseif (is_numeric($string)) {
            return $string;
        }
        else {
            $string = (array) $string;
            if (isset($string[0])) {
                return htmlentities($string[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        return false;
    }
}
