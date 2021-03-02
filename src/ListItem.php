<?php

namespace megachriz\ListConvert;

/**
 * Represents an item on a list.
 */
class ListItem {

  /**
   * The number of this item in the list.
   *
   * @var int
   */
  private $index;

  /**
   * A series of child lists.
   *
   * @var \megachriz\ListConvert\OrderedList[]
   */
  private $lists = [];

  /**
   * The text of the list item.
   *
   * @var string
   */
  protected $value;

  /**
   * Constructs a new ListItem object.
   *
   * @param int $index
   *   The number of this item in the list.
   */
  public function __construct(int $index) {
    $this->index = $index;
  }

  /**
   * Adds a list to the item.
   *
   * @param \megachriz\ListConvert\OrderedList $list
   *   The list to add.
   */
  public function addList(OrderedList $list) {
    $this->lists[] = $list;
  }

  /**
   * Returns the lists that this item contains.
   *
   * @return \megachriz\ListConvert\OrderedList[]
   *   A series of child lists.
   */
  public function getLists(): array {
    return $this->lists;
  }

  /**
   * Sets the text of this list item.
   *
   * @param string $value
   *   The value to set.
   *
   * @return \megachriz\ListConvert\ListItem
   *   An instance of this class.
   */
  public function setValue(string $value): ListItem {
    $this->value = $value;
    return $this;
  }

  /**
   * Returns the text of this list item.
   *
   * @return string
   *   The text for this list item.
   */
  public function getValue(): string {
    return $this->value;
  }

  /**
   * Renders the list item as plain text.
   *
   * @param int $level
   *   (optional) How much indent should be added to the list item.
   *
   * @return string
   *   The list item, rendered as plain text.
   */
  public function render($level = 0) {
    $result = $this->value;
    foreach ($this->lists as $list) {
      $result .= "\n\n" . $list->render($level + 1);
    }
    return $result;
  }

}
