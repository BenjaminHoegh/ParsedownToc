![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/parsedown-toc.svg?style=flat-square)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/parsedown-toc.svg?style=flat-square)

# Parsedown-toc
Table of content for [Parsedown](https://github.com/erusev/parsedown)

## Usage

- Option 1:

  You can simply use `[toc]` inside your markdown where you want it to make the list.
  
- Option 2:

  If you like to get a toc outside the markdown you can use `toc()` 
  this requires that you define a source
  
  **Example:**
  ```php
  $markdown = file_get_contents('test1.md');
  
  echo $Parsedown->toc($markdown);

  echo $Parsedown->text($markdown);
  ```


## Options

You have some options to play with `selector`, `scope` and `inline`. These options can be set and used with `toc()` 

- `Inline`

  Use to toggle the use of `[toc]` inside your markdown.

- `Selector`

  Chose which headers to catch.

- `Scope`

  Define the source to make a toc off, you can example use an external source or the markdown source itself

**Examples:**

- Using to change settings for inline toc

  ```php
  $body = file_get_contents('test1.md');
  $Parsedown->toc([
      'selector' => ['h1','h2','h3','h4','h5','h6'],
      'inline' => true,
  ]);
  echo $Parsedown->text($body);
  ```

- Used outsite of the document with settings


  ```php
  $body = file_get_contents('test1.md');
  echo $Parsedown->toc([
      'selector' => ['h1','h2','h3','h4','h5','h6'],
      'inline' => false,
      'scope' => $body
  ]);
  echo $Parsedown->text($body);
  ```

- Used to change settings with toc outsite of document

  ```php
  $body = file_get_contents('test1.md');
  $Parsedown->toc([
      'selector' => ['h1','h2','h3','h4','h5','h6'],
      'inline' => false,
  ]);

  echo $Parsedown->toc($body);
  echo $Parsedown->text($body);
  ```

---

## ParsedownExtra

If you wanna use it with ParsedownExtra you need to change the following line:
```
class ParsedownToc extends Parsedown {
```
to
```
class ParsedownToc extends ParsedownExtra {
```


---

### About setext headers

To avoid too many cycles there slow down the rendering headers using `---` or `===` require at least 3 `-` or `=` to get detected (working on a solution to solve this)


---

Any help on documentation are welcome as my english isn't the best
