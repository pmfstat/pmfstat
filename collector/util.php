<?php

class Questionaire_CollectionInformation extends FilterIterator {
    private $o;
    private $key;

    public function __construct(Questionnaire_Data $o) {
        $this->o = $o;
        $ro = new ReflectionObject($o);
        parent::__construct(new ArrayIterator($ro->getMethods()));
    }

    public function accept() {
       $retval = preg_match('/^get([a-zA-Z0-9]+)Info$/', parent::current()->getName(), $matches);
       if ($retval) {
           $this->key = $matches[1];
       }
       return $retval;
    }

    public function key() {
        return $this->key;
    }

    public function current() {
        return parent::current()->invoke($this->o);
    }
}

