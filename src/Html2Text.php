<?php

namespace megachriz\ListConvert;

use voku\helper\HtmlDomParser;
use voku\helper\SimpleHtmlDom;
use voku\helper\UTF8;
use voku\Html2Text\Html2Text as Html2TextBase;

/**
 * Class Html2Text with additional support for ordered lists.
 */
class Html2Text extends Html2TextBase {

  /**
   * {@inheritdoc}
   */
  protected function convertLists(&$text) {
    $dom = HtmlDomParser::str_get_html($text);
    foreach ($dom->find('ol,ul') as $list) {
      $ol = $this->parseList($list);
      $find = $list->outerHtml();
      $replace = $ol->render();

      $replace = str_replace("\n", '<br />', $replace);
      $replace = str_replace(" ", '&nbsp;', $replace);

      $text = str_replace($find, $replace, $text);
    }
  }

  /**
   * Parses a list: <ol> and <ul>.
   *
   * @param \voku\helper\SimpleHtmlDom $list
   *   The dom element of the list.
   *
   * @return \megachriz\ListConvert\OrderedList
   *   The generated list object.
   */
  protected function parseList(SimpleHtmlDom $list) {
    $type = OrderedList::TYPE_NUMBER;
    if ($list->getNode()->tagName == 'ul') {
      $type = OrderedList::TYPE_UNORDERED;
    }
    if ($list->getAttribute('type')) {
      $type = $list->getAttribute('type');
    }

    $ol = new OrderedList($type);

    // Check if the list starts at a specific number.
    $start = $list->getAttribute('start');
    $index = $start ? $start -1 : 0;

    foreach ($list->childNodes() as $child) {
      if (!$child->getNode() instanceof \DOMElement) {
        continue;
      }
      if ($child->getNode()->tagName != 'li') {
        continue;
      }

      $value = $child->getAttribute('value');
      if (!empty($value) && is_numeric($value)) {
        $index = (int) $value;
      }
      else {
        $index++;
      }

      /** @var \megachriz\ListConvert\ListItem $item */
      $item = $ol->add($index);
      $this->parseListItem($item, $child);
    }

    return $ol;
  }

  /**
   * Parses <li> from html.
   *
   * @param \megachriz\ListConvert\ListItem $item
   *   The list item object to add parsed data to.
   * @param \voku\helper\SimpleHtmlDom $list_item
   *   The dom element of the list item.
   */
  protected function parseListItem(ListItem $item, SimpleHtmlDom $list_item) {
    $html = $list_item->innerHtml();

    // Remove any ols and uls from html.
    $dom = HtmlDomParser::str_get_html($html);
    foreach ($dom->find('ol,ul') as $list) {
      $find = $list->outerHtml();
      $replace = '';
      $html = str_replace($find, $replace, $html);
    }

    $item->setValue($html);

    // Override the type of list, if it has one.
    $type = $list_item->getAttribute('type');
    if ($type) {
      $item->setType($type);
    }

    foreach ($list_item->childNodes() as $child) {
      if (!$child->getNode() instanceof \DOMElement) {
        continue;
      }

      switch ($child->getNode()->tagName) {
        case 'ol':
        case 'ul':
          $item->addList($this->parseList($child));
          break;
      }
    }
  }

  /**
   * Replaces lists in text with summary of the list items.
   *
   * @return string
   *   The HTML, where lists a replaced with a comma separated string of item markers.
   */
  public function getListSummary() {
    $dom = HtmlDomParser::str_get_html($this->html);
    // Convert text back to html.
    $text = $dom->outerHtml();
    foreach ($dom->find('ol,ul') as $list) {
      $ol = $this->parseList($list);
      $find = $list->outerHtml();

      $numbers = $this->getItemMarkersFromList($ol);
      if (!empty($numbers)) {
        $replace = '<p>' . implode(', ', $numbers) . '</p>';
      }
      else {
        $replace = '';
      }

      $text = str_replace($find, $replace, $text);
    }

    return $text;
  }

  /**
   * Generates a list of item markers.
   *
   * @param \megachriz\ListConvert\OrderedList $ol
   *   The list to get the item markers from.
   *
   * @return string[]
   *   A list of item markers.
   */
  protected function getItemMarkersFromList(OrderedList $ol): array {
    if ($ol->getType() == OrderedList::TYPE_UNORDERED) {
      return [];
    }

    $numbers = [];

    foreach ($ol->getItems() as $index => $item) {
      $type = $item->getType() ?? $ol->getType();
      $number = $ol->renderIndex($index, $type);
      $lists = $item->getLists();
      if (empty($lists)) {
        $numbers[] = $number;
      }
      else {
        foreach ($lists as $list) {
          $subnumbers = $this->getItemMarkersFromList($list);
          switch ($list->getType()) {
            case OrderedList::TYPE_ROMAN:
            case OrderedList::TYPE_ROMAN_UC:
              $number .= '-';
              break;

            default:
              switch ($ol->getType()) {
                case OrderedList::TYPE_ROMAN:
                case OrderedList::TYPE_ROMAN_UC:
                  $number .= '-';
                  break;
              }
              if ($ol->getType() == $list->getType()) {
                $number .= '-';
              }
              break;
          }

          // Prepend current number for each subnumber.
          foreach ($subnumbers as $key => $value) {
            $subnumbers[$key] = $number . $value;
          }

          $numbers = array_merge($numbers, $subnumbers);
        }
      }
    }

    return $numbers;
  }

}
