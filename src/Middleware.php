<?php

namespace ZhangYiJiang\ArrayRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Middleware
{
    public function handle(Request $request, \Closure $next, ...$ignored)
    {
        foreach ($request->all() as $key => $value) {
            if (is_array($value) && ! Arr::isAssoc($value) && ! in_array($key, $ignored)) {
                $keys = [];
                $valid = true;

                foreach ($value as $col) {
                    if (!is_array($col) || count($col) > 1) {
                        $valid = false;
                        break;
                    }

                    $row = $col[0];
                    $v = reset($row);
                    $keys[key($row)][] = $v;
                }

                if ($valid) {
                    $values = array_map(null, ...array_values($keys));
                    $values = array_map(function ($v) use ($keys){
                        return array_combine(array_keys($keys), $v);
                    }, $values);

                    $request[$key] = $values;
                }
            }
        }

        return $next($request);
    }
}