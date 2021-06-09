<?Php
//DOMDocument_createElement_simple
class DOMDocumentCustom extends DOMDocument
{
    function __construct($version = null, $encoding = null) {
        parent::__construct($version, $encoding);
        //$this->registerNodeClass('DOMElement', 'XDOMElement');
    }

	public function createElement_simple($tagName,$parent=false,$attributes='',$value=false)
	{
		if($tagName!==false)
		{
			$element=$this->createElement($tagName);
			if($value!==false && is_string($value))
				$element->appendChild($this->createTextNode($value)); //http://stackoverflow.com/questions/22956330/domdocumentcreateelement-unterminated-entity-reference
		}
		else
			$element=$this->createTextNode($value);
		if(is_array($attributes))
		{
			foreach($attributes as $name=>$value)
			{
				$element->setAttribute($name,$value);
			}
		}
		if($parent!==false)
			$parent->appendChild($element);
		return $element;
	}
}
