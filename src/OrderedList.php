<?php

namespace megachriz\ListConvert;

/**
 * Represents a list.
 */
class OrderedList {

  /**
   * List types.
   *
   * @var string
   */
  const TYPE_NUMBER = 1;
  const TYPE_ALPHA = 'a';
  const TYPE_ALPHA_UC = 'A';
  const TYPE_ROMAN = 'i';
  const TYPE_ROMAN_UC = 'I';
  const TYPE_UNORDERED = 'ul';

  /**
   * The type of ordered list.
   *
   * Can be:
   * - 1
   * - a
   * - A
   * - i
   * - I
   *
   * @var string
   */
  private $type;

  /**
   * The contents of the list.
   *
   * @var \megachriz\ListConvert\ListItem[]
   */
  private $items = [];

  /**
   * Constructs a new OrderedList object.
   *
   * @param string $type
   *   The type of list.
   */
  public function __construct($type = 1) {
    $this->type = $type;
  }

  /**
   * Adds a new item to the list.
   *
   * @param int $index
   *   The index number for the list.
   *
   * @return \megachriz\ListConvert\ListItem
   *   The created list item.
   */
  public function add(int $index): ListItem {
    $item = new ListItem($index);
    $this->items[$index] = $item;

    return $item;
  }

  /**
   * Returns the items of this list.
   *
   * @return \megachriz\ListConvert\ListItem[]
   *   The contents of the list.
   */
  public function getItems(): array {
    return $this->items;
  }

  /**
   * Returns the list type.
   *
   * @return string
   *   The type of list.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Renders the list as plain text.
   *
   * @param int $level
   *   (optional) How much indent should be added to the list items.
   *
   * @return string
   *   The list, rendered as plain text.
   */
  public function render($level = 1) {
    $result = '';
    $indent = '';
    $indent = str_pad($indent, $level * 2, ' ', STR_PAD_LEFT);
    foreach ($this->items as $index => $item) {
      $result .= $indent;
      $result .= $this->renderIndex($index);
      if ($this->type != static::TYPE_UNORDERED) {
        $result .= '.';
      }
      $result .= ' ';
      $result .= $item->render($level);
      $result .= "\n";
    }
    return $result;
  }

  /**
   * Renders the index number.
   *
   * @param int $index
   *   The index number to render.
   *
   * @return string
   *   The rendered index number.
   */
  public function renderIndex(int $index): string {
    switch ($this->type) {
      case static::TYPE_ALPHA:
        return strtolower($this->num2alpha($index));

      case static::TYPE_ALPHA_UC:
        return $this->num2alpha($index);

      case static::TYPE_ROMAN:
        return strtolower($this->num2roman($index));

      case static::TYPE_ROMAN_UC:
        return $this->num2roman($index);

      case static::TYPE_UNORDERED:
        return '*';

      default:
        return (string) $index;
    }
  }

  /**
   * Converts a number to an alpha character.
   *
   * @param int $n
   *   The number to convert.
   *
   * @return string
   *   The alpha character.
   */
  protected function num2alpha(int $n): string {
    $n--;
    $r = '';
    for ($i = 1; $n >= 0 && $i < 10; $i++) {
      $r = chr(0x41 + ($n % pow(26, $i) / pow(26, $i - 1))) . $r;
      $n -= pow(26, $i);
    }
    return $r;
  }

  /**
   * Converts a number to a roman number.
   *
   * @param int $n
   *   The number to convert.
   *
   * @return string
   *   The roman number.
   */
  protected function num2roman(int $n): string {
    $map = [
      'M' => 1000,
      'CM' => 900,
      'D' => 500,
      'CD' => 400,
      'C' => 100,
      'XC' => 90,
      'L' => 50,
      'XL' => 40,
      'X' => 10,
      'IX' => 9,
      'V' => 5,
      'IV' => 4,
      'I' => 1,
    ];
    $r = '';
    while ($n > 0) {
      foreach ($map as $roman => $int) {
        if ($n >= $int) {
          $n -= $int;
          $r .= $roman;
          break;
        }
      }
    }
    return $r;
  }

}
