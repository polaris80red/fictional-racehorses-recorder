<?php
class FlagChecker{
    protected int $flags;
    public function __construct(int $flags) {
        $this->setFlag($flags);
    }
    public function setFlag(int $flags) {
        $this->flags = $flags;
        return $this;
    }
    /**
     * $flagのビットがすべてONならtrue|それ以外はfalse
     */
    public function hasFlag(int $flag) {
        return ($this->flags & $flag) === $flag;
    }
    /**
     * どれか1つでもONならtrue
     * @return bool ONがある=true|すべてOFF=false
     */
    public function hasAnyFlag(array $flags) {
        foreach ($flags as $flag) {
            if (($this->flags & $flag) === $flag) {
                return true;
            }
        }
        return false;
    }
    /**
     * すべてONのときだけTrue、OFFが含まれていればfalse
     * @return bool すべてON=true|OFFを含む=false
     */
    public function hasAllFlags(array $flags) {
        foreach ($flags as $flag) {
            if (($this->flags & $flag) !== $flag) {
                return false;
            }
        }
        return true;
    }
}
