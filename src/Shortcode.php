<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode
    {
    private $name;
    private $parameters;
    private $content;
    private $text;

    public function __construct($name, array $arguments, $content)
        {
        $this->name = $name;
        $this->parameters = $arguments;
        $this->content = $content;
        }

    public function asText()
        {
        if(null === $this->text)
            {
            $this->text = $this->createText();
            }

        return $this->text;
        }

    private function createText()
        {
        return
            '['.$this->name.$this->createParametersText().']'
            .(null === $this->content ? '' : $this->content.'[/'.$this->name.']');
        }

    private function createParametersText()
        {
        $return = '';
        foreach($this->parameters as $key => $value)
            {
            $return .= ' '.$key;
            if(null !== $value)
                {
                $return .= '='.(preg_match('/^\w+$/us', $value) ? $value : '"'.$value.'"');
                }
            }

        return $return;
        }

    public function getName()
        {
        return $this->name;
        }

    public function getParameters()
        {
        return $this->parameters;
        }

    public function getContent()
        {
        return $this->content;
        }
    }
