<?php
namespace GXChain\Common;

class Validator
{
    public function validate($params, $schema) {
        $result = (object)[];
    
        if (!is_object($params)) {
            throw new \Exception('params ' . $params . ' should be an object');
        }

        if (!is_object($schema)) {
            throw new \Exception('schema ' . $schema . ' should be an object');
        }

        foreach ($schema as $key => $val) {
            $type = $val->type;
            $value = $params->$key;
            if(isset($val->defaultsTo)) $defaultValue = $val->defaultsTo;

            // assign a default value if the value is not assigned
            if (is_null($value) && !is_null($defaultValue)) {
                $value = $defaultValue;
                $result->$key = $value;
            }
            // throw an error if value is required but not assigned
            if (is_null($value) && $val->required) {
                throw new \Exception($key . ' in params is required');
            }

            switch ($type) {
                case "integer":
                    if (!is_integer($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    } else {
                        $result->$key = $value;
                    }
                    break;
                case "string":
                    if (!is_string($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    } else {
                        $result->$key = $value;
                    }
                    break;
                case "boolean":
                    if (!is_bool($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    } else {
                        $result->$key = $value;
                    }
                    break;
                case "number":
                    if (!is_numeric($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    } else {
                        $result->$key = $value;
                    }
                    break;
                case "json":
                    if (!is_object($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    }
                    $result->$key = self::validate($value, $schema->fields || (object)[]);
                    break;
                case "array":
                    if (!is_array($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    } else if (!$val->columns) {
                        throw new \Exception('columns definition should be assigned for ' . $key);
                    } else if (!is_object($val->columns)) {
                        throw new \Exception($key . ' should be an instance of array');
                    } else {
                        $result->$key = array();
                        foreach ($value as $item) {
                            array_push($result->$key, self::validate($item, $val->columns));
                        }
                    }
                    break;
                case "raw":
                    if (!is_object($value)) {
                        throw new \Exception($key . ' should be a type of ' . $type);
                    }
                    $result->$key = $value;
                    break;
                default:
                    throw new \Exception('unknown type ' . $type . ' found in params, which is not supported at this time, will be ignored');
            }
        }
        
        return $result;
    }
}
?>
