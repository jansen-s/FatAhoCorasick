<?php

namespace FatAhoCorasick;

class FatAhoCorasick
{
    protected $maxState = 0;
    
    // keyword list
    protected $keywords = [];
    
    // goto table
    protected $goto = [];
    
    // output table
    protected $output = [];
    
    //failure table
    protected $failure = [];
    
    // next table
    protected $next = [];
    
    public function __construct()
    {

    }
    
    public function addKeyword($keyword)
    {
        if (is_array($keyword)) {
            foreach ($keyword as $realKeyword) {
                $this->keywords[] = (string)$realKeyword;
            }
        } else {
            $this->keywords[] = (string)$keyword;
        }
    }
    
    public function compute()
    {
        $this->reset();
        $this->computeGoto();
        $this->computeFailure();
        $this->computeNext();
    }
    
    protected function reset()
    {
        $this->goto = $this->failure = $this->output = $this->next = [];
    }
    
    protected function computeGoto()
    {
        $this->maxState = 0;
        foreach ($this->keywords as $keyword) {
            $this->enter($keyword);
        }
    }
    
    protected function enter(string $keyword)
    {
        $state = 0;
        $len = strlen($keyword);
        $i = 0;
        while (isset($this->goto[$state][$keyword[$i]])) {
            $state = $this->goto[$state][$keyword[$i]];
            $i++;
        }
        for (; $i < $len; $i++) {
            $this->goto[$state][$keyword[$i]] = ++$this->maxState;
            $state = $this->maxState;
        }
        $this->output[$state][] = $keyword;
    }
    
    protected function computeFailure()
    {
        $queue = [];
        foreach ($this->goto[0] as $char => $toState) {
            $this->failure[$toState] = 0;
            $queue[] = $toState;
        }
        while ($queue) {
            $nextState = array_shift($queue);
            if ( ! isset($this->goto[$nextState])) {
                continue;
            }
            foreach ($this->goto[$nextState] as $char => $toState) {
                $queue[] = $toState;
                $tempState = $this->failure[$nextState];
                while($tempState !== 0 && ! isset($this->goto[$tempState][$char])) {
                    $tempState = $this->failure[$tempState];
                }
                $this->failure[$toState] = $this->goto[$tempState][$char] ?? 0;
                if (isset($this->output[$this->failure[$toState]])) {
                    $this->output[$toState] = array_merge($this->output[$toState], $this->output[$this->failure[$toState]]);
                }
            }
        }
    }
    
    protected function computeNext()
    {
        
    }
    
    public function search($string)
    {
        $result = [];
        $state = 0;
        $len = strlen($string);
        
        for ($i = 0; $i < $len; $i++) {
            while($state !==0 && ! isset($this->goto[$state][$string[$i]])) {
                $state = $this->failure[$state] ?? 0;
            }
            $state = $this->goto[$state][$string[$i]] ?? 0;
            if (isset($this->output[$state])) {
                $result[] = [$i, $this->output[$state]];
            }
        }
        
        return $result;
    }
}