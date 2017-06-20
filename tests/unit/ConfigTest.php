<?php

namespace Nextform\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Nextform\Config\XmlConfig;
use NextForm\Config\AutoConfig;
use Nextform\Fields\InputField;
use Nextform\Fields\AbstractField;
use Nextform\Fields\TextareaField;
use Nextform\Fields\FormField;
use Nextform\Fields\FieldCollection;
use Nextform\Fields\Validation\ValidationModel;

class ConfigTest extends TestCase
{
	/**
	 *
	 */
	protected function setUp() {
		$this->validXmlFile = realpath(__DIR__ . '/../assets/sample.xml');
		$this->validXmlFileSelect = realpath(__DIR__ . '/../assets/select.xml');
		$this->invalidXmlFile = realpath(__DIR__ . '/../assets/invalidfile.xml');
		$this->invalidXmlExtensionFile = realpath(__DIR__ . '/../assets/sample.fml');
		$this->nonExistentXmlFile = '../assets/nonexitence.xml';
	}

	/**
     * @expectedException Nextform\Parser\Exception\LogicException
     * @expectedExceptionMessage Invalid file given
	 */
	public function testXmlConfigInvalidFileLoad() {
		new XmlConfig($this->invalidXmlFile);
	}

	/**
     * @expectedException Nextform\Parser\Exception\LogicException
     * @expectedExceptionMessage The file "../assets/nonexitence.xml" was not found
	 */
	public function testXmlConfigNonExistentFileLoad() {
		new XmlConfig($this->nonExistentXmlFile);
	}

	/**
	 * @expectedException Nextform\Parser\Exception\LogicException
	 * @expectedExceptionMessage Parser for "fml" file not found
	 */
	public function testAutoConfigInvalidParser() {
		new AutoConfig($this->invalidXmlExtensionFile);
	}

	/**
	 * @expectedException Nextform\Parser\Exception\LogicException
	 * @expectedExceptionMessage You need to use a specific config if you parse content.
	 */
	public function testXmlAutoConfigContentInput() {
		new AutoConfig('{"jsonstring": "thisshouldbeinvalid"}', true);
	}

	/**
	 * @expectedException Nextform\Parser\Exception\LogicException
	 * @expectedExceptionMessage Invalid file extension. "xml" needed. "fml" found
	 */
	public function testXmlConfigInvalidFileExtension() {
		new XmlConfig($this->invalidXmlExtensionFile);
	}

	/**
	 * @expectedException Nextform\Parser\Exception\InvalidConfigException
	 * @expectedExceptionMessage The parsed config is invalid
	 */
	public function testXmlConfigInvalidContentInputStdIn() {
		new XmlConfig('<form></form>', true);
	}

	/**
	 *
	 */
	public function testXmlConfigValidContentInput() {
		$config = new XmlConfig(file_get_contents($this->validXmlFile), true);

		$this->assertTrue($config->getFields() instanceof FieldCollection);
	}

	/**
	 *
	 */
	public function testAutoConfigFieldsIsCollection() {
		$config = new AutoConfig($this->validXmlFile);

		$this->assertTrue($config->getFields() instanceof FieldCollection);
	}

	/**
     *
	 */
	public function testXmlConfigFieldsIsCollection() {
		$xmlConfig = new XmlConfig($this->validXmlFile);

		$this->assertTrue($xmlConfig->getFields() instanceof FieldCollection);
	}

	/**
	 * @return array
	 */
	private function getXmlConfigFields() {
		$xmlConfig = new XmlConfig($this->validXmlFile);

		return $xmlConfig->getFields();
	}

	/**
	 *
	 */
	public function testXmlConfigFieldsValidField() {
		$fields = $this->getXmlConfigFields();

		$this->assertTrue($fields->get('firstname') instanceof InputField);
		$this->assertTrue($fields->get('lastname') instanceof InputField);
		$this->assertTrue($fields->get('description') instanceof TextareaField);
	}

	/**
	 *
	 */
	public function testXmlConfigFieldsInvalidField() {
		$fields = $this->getXmlConfigFields();

		$this->assertTrue(is_null($fields->get('invalidfield')));
	}

	/**
	 *
	 */
	public function testXmlConfigFieldsValidFieldAttributeType() {
		$fields = $this->getXmlConfigFields();
		$firstname = $fields->get('firstname');

		$this->assertTrue(is_string($firstname->getAttribute('type')));
	}

	/**
	 *
	 */
	public function testXmlConfigFieldsValidFieldAttributeValue() {
		$fields = $this->getXmlConfigFields();
		$firstname = $fields->get('firstname');
		$lastname = $fields->get('lastname');

		$this->assertTrue($firstname->getAttribute('type') == 'text');
		$this->assertTrue($lastname->getAttribute('type') == 'text');
	}

	/**
	 * @expectedException Nextform\Fields\Exception\AttributeNotFoundException
	 * @expectedExceptionMessage Attribute "invalidattribute" not found
	 */
	public function testXmlConfigFieldsInvalidFieldAttribute() {
		$fields = $this->getXmlConfigFields();
		$description = $fields->get('description');

		$description->getAttribute('invalidattribute');
	}

	/**
	 *
	 */
	public function testXmlConfigValidationFieldExistence() {
		$fields = $this->getXmlConfigFields();
		$firstname = $fields->get('firstname');
		$description = $fields->get('description');

		$this->assertTrue($firstname->getValidation('required') instanceof ValidationModel);
		$this->assertTrue($description->getValidation('required') instanceof ValidationModel);
	}

	/**
	 *
	 */
	public function testXmlConfigValidationFieldErrorMessage() {
		$fields = $this->getXmlConfigFields();
		$firstname = $fields->get('firstname');
		$description = $fields->get('description');

		$firstnameValidation = $firstname->getValidation('minlength');
		$descriptionValidation = $description->getValidation('maxlength');

		$this->assertEquals(
			'Default maxlength error',
			$descriptionValidation->error
		);

		$this->assertEquals(
			'Too short. ' . $firstnameValidation->value . ' characters at least',
			$firstnameValidation->error
		);
	}

	/**
	 *
	 */
	public function testXmlConfigTraversable() {
		$fields = $this->getXmlConfigFields();

		foreach ($fields as $field) {
			$this->assertTrue($field instanceof AbstractField);
		}
	}

	/**
	 *
	 */
	public function testXmlConfigCountable() {
		$fields = $this->getXmlConfigFields();

		$this->assertEquals(3, count($fields));
	}

	/**
	 *
	 */
	public function testXmlConfigRootNode() {
		$fields = $this->getXmlConfigFields();

		$this->assertTrue($fields->getRoot() instanceof FormField);
		$this->assertEquals('test.php', $fields->getRoot()->getAttribute('action'));
	}

	/**
	 *
	 */
	public function testXmlConfigYieldChildrenLayerOne() {
		$xmlConfig = new XmlConfig($this->validXmlFileSelect);
		$xmlFields = $xmlConfig->getFields();
		$selectField = $xmlFields->get('gender');

		$this->assertTrue(is_array($selectField->getChildren()));
		$this->assertTrue(count($selectField->getChildren()) > 1);
		$this->assertTrue($selectField->hasChildren());
	}

	/**
	 *
	 */
	public function testXmlConfigYieldChildrenLayerOneContent() {
		$xmlConfig = new XmlConfig($this->validXmlFileSelect);
		$xmlFields = $xmlConfig->getFields();
		$selectField = $xmlFields->get('price');

		foreach ($selectField->getChildren() as $child) {
			$this->assertTrue( ! empty($child->getContent()));
		}
	}

	/**
	 *
	 */
	public function testXmlConfigYieldChildrenLayerOneAttribute() {
		$xmlConfig = new XmlConfig($this->validXmlFileSelect);
		$xmlFields = $xmlConfig->getFields();
		$selectField = $xmlFields->get('price');

		foreach ($selectField->getChildren() as $child) {
			$this->assertTrue( ! empty($child->getAttribute('key')));
		}
	}
}