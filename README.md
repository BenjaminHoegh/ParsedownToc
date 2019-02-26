# Parsedown-toc
Table of content for Parsedown

## Usage

To use toc you simple use `[toc]` in your markdown file where you want it to make the list.


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

To avoid to many cyclus there slow down the rendering headers using `---` or `===` require at least 3 `-` or `=` to get detected
