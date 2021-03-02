List Converter
==============
This library extends [Html2Text](https://github.com/voku/html2text) by adding support for converting ordered lists.

Additionally, it also has a feature for *summarizing* html lists, useful if you automatically want to create references to all items from a list.

### Basic Usage
```php
$html = '
<ol>
  <li>First item</li>
  <li>
    Second item
    <ol type="a">
      <li value="3">Item C</li>
      <li>Item D</li>
    </ol>
  </li>
  <li>Third item</li>
</ol>';
$converter = new \megachriz\ListConvert\Html2Text($html);

print $converter->getText();
```

Will result into:
```
  1. First item
  2. Second item

    c. Item C
    d. Item D

  3. Third item
```

### Summarize a list
```php
$html = '
<ol>
  <li>First item</li>
  <li>
    Second item
    <ol type="a">
      <li value="3">Item C</li>
      <li>Item D</li>
    </ol>
  </li>
  <li>
    Third item
    <ol type="I">
      <li>Alpha</li>
      <li>Beta</li>
      <li>
        <ol type="a">
          <li>Item A</li>
          <li>Item B</li>
        </ol>
      </li>
    </ol>
  </li>
</ol>';
$converter = new \megachriz\ListConvert\Html2Text($html);

print $converter->getListSummary();
```

Will result into:
```
<p>1, 2c, 2d, 3-I, 3-II, 3-III-a, 3-III-b</p>
```
