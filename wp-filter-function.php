<?php

class SortDirection {
    const Ascending = 1;
    const Descending = -1;
}

interface Filter {
    public function filter($item);
}

interface Comparator {
    public function compare($a, $b);
}

class MultipleKeyValueFilter implements Filter {

    protected $kvPairs;

    public function __construct($kvPairs) {
        $this->kvPairs = $kvPairs;
    }

    public function filter($item) {
        $result = true;

        foreach ($this->kvPairs as $key => $value) {
            if ($item[$key] !== $value)
                $result &= false;
        }

        return $result;
    }
}

class KeyComparator implements Comparator {
    protected $direction;
    protected $transform;
    protected $key;

    public function __construct($key, $direction = SortDirection::Ascending, $transform = null) {
        $this->key = $key;
        $this->direction = $direction;
        $this->transform = $transform;
    }

    public function compare($a, $b) {
        $a = $a[$this->key];
        $b = $b[$this->key];

        if ($this->transform) {
            $a = $this->transform($a);
            $b = $this->transform($b);
        }

        return $a === $b ? 0 : (($a > $b ? 1 : -1) * $this->direction);
    }
}

class MultipleKeyComparator implements Comparator {
    protected $keys;

    public function __construct($keys) {
        $this->keys = $keys;
    }

    public function compare($a, $b) {
        $result = 0;

        foreach ($this->keys as $comparator) {
            if ($comparator instanceof KeyComparator) {
                $result = $comparator->compare($a, $b);

                if ($result !== 0) return $result;
            }
        }

        return $result;
    }
}

//$array = array (
//    '1' => array ('type' => 'blah2', 'category' => 'cat2', 'exp_range' => 'this_week' ),
//    '2' => array ('type' => 'blah1', 'category' => 'cat1', 'exp_range' => 'this_week' ),
//    '3' => array ('type' => 'blah1', 'category' => 'cat2', 'exp_range' => 'next_week' ),
//    '4' => array ('type' => 'blah1', 'category' => 'cat1', 'exp_range' => 'next_week' )
//);
//
//
//
//$filter = new MultipleKeyValueFilter(array(
//    'type' => 'blah1',
//    'category' => 'cat2',
//));
//
//echo "Filtered by multiple fields\n";
//dump(array_filter($array, array($filter, 'filter')));