<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ValidMacAddress implements ValidationRule
{
    private bool $strictMode = false;

    public function __construct(int $flag = 0)
    {
        $this->strictMode = boolval($flag & config('freeswitch.STRICT_MAC_ADDREDSS'));
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!ctype_xdigit($value)){
            $fail(__('Non HEXDECIMAL characters detected.'));
        }
        else{
            if (strval($value) == "000000000000"){
                // We allow this as universal wildcard
                if(App::hasDebugModeEnabled()){
                    Log::debug('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] 000000000000 Mac address');
                }
            }
            elseif ($this->strictMode){
                $found = false;
                $oid = substr($value, 0, 6);
                if (strlen($oid) == 6){
                    $response = Http::get('https://standards-oui.ieee.org/oui/oui.txt');
                    if ($response->ok){
                        $lines = explode("\n", $response->body());
                        foreach ($lines as $line){

                                $oidTest = substr(trim($line), 0, 6);
                                $l = strlen($oidTest);
                                if (ctype_xdigit($oidTest)){
                                        if ($l == 6){
                                            if (!strcmp($oidTest, $oid)){
                                                if(App::hasDebugModeEnabled()){
                                                    Log::debug('['.__FILE__.':'.__LINE__.']['.__CLASS__.']['.__METHOD__.'] '. $oid. ' found.');
                                                }
                                                $found = true;
                                                break;
                                            }
                                        }
                                }
                        }

                        if (!$found)
                        {
                            $fail(__('MAC Address OID not found.'));
                        }
                    }
                    else
                    {
                        $fail(__('Cannot download the OUI list.'));
                    }
                }
                else
                {
                    $fail(__('Error on the OID lenght.'));
                }

            }
        }
    }
}
