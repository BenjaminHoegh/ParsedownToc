# Parsedown-toc
Table of content for Parsedown

## Usage

To use toc you simple use `[toc]` in your markdown file where you want it to make the list.


* Markdown:
  ```
  [toc]

  ---

  # Head1
  Sample text of head 1.

  ## Head1-1
  Sample text of head 1-1.
  ```

* Result:

  ```
  <ul>
    <li><a href="#Head1">Head1</a>
      <ul>
        <li><a href="#Head1-1">Head1-1</a></li>
      </ul>
    </li>
  </ul>

  <hr>

  <h1 id="Head1">Head1</h1>
  <p>Sample text of head 1.</p>

  <h2 id="Head1-1">Head1-1</h2>
  <p>Sample text of head 1-1.</p>
  ```

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

To avoid to many cyclus there slow down the rendering headers using `---` or `===` require at least 3 `-` or `=` to get detected (working on a solution to solve this)
