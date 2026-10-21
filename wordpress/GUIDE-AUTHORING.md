# Writing a guide in the block editor

A new guide is an ordinary post (Posts > Add New). No custom fields hold content.

## 1. Settings (right sidebar, "Guide settings")

| Setting | What it does |
|---|---|
| Topic | Groups the guide on the blog hub and picks its colour. |
| Standfirst | The line under the headline (also the card text). |
| Updated on | Optional. Shown as "Matches the app as of …" instead of the publish date. |
| Cover screen | The product screen used on blog cards. |
| Featured guide | Puts it in the featured slot on the hub. |

**Featured image** (right sidebar, "Featured image"): shown as a wide photo under the hero (cropped to about 21:9, so upload at least 1200 px wide and keep the subject in the middle) and used on the blog cards instead of the cover screen. Fill in its **Alt text** and **Caption** (the caption prints under the photo; say "Sample screen" for a product screen). It is also the share image on social networks. Without one, the guide has no photo and cards show the cover screen.

The read time is **counted from the text** (200 words a minute) when the post is saved, and the guide's own page recounts it. Do not type it in.

## 2. The body

Use normal blocks for text: Paragraph, Heading 2 (each one becomes an entry in "On this page"), Heading 3, List, Quote, Image (always add a caption on product screens), Separator.

Use the **Healthray guide** blocks (inserter > "Healthray guide") for components. Or insert the pattern **Guide: starter**.

| Block | Use it for |
|---|---|
| In short | The short answer. The **first one** in a guide is lifted into the hero as "The short version" (3–5 list items). |
| Note | Tip, Note, Watch out, Example (type in the sidebar). One idea per box. |
| Rule of thumb | One rule, set large. |
| Checklist | Printable tick list (a Print button is added). |
| Steps | Numbered steps; each has a title, text and an optional "when" line. |
| Comparison | Two options with tags, rows, an optional winner (sidebar) and a verdict. |
| Worked sum | A calculation line by line with a result. Label made-up numbers as illustrative. |
| Call to action | Inline link, "How the software handles this" feature card, or the demo panel. |
| FAQ | Questions and answers; also sent to search engines as FAQ data, so write only answers you are sure of. |
| Table (core) | Pick a style in the sidebar: Guide table, numbers right, highlight column 1 / 2 / last. Turn on "Header section". |

## 3. The sample

`Posts > Guide components: every block in one sample guide` shows every component. Copy a block (or select several) into your guide and change the words. It is noindex and hidden from the blog; `php wordpress\import\blog-components-sample.php` re-creates it.

## 4. Changing how a component looks

- Markup: `inc/blog-components.php` (one renderer per component, shared with the six older guides).
- Look: `assets/css/blog.css` (front) and `assets/css/blog-editor.css` (editor only).
- Blocks and their fields: `inc/blog-blocks.php` (server) and `assets/js/blog-editor.js` (editor, no build step).

Because the blocks are dynamic, a change in one place updates every guide.

## 5. The six older guides

They keep their content in the SCF `body` field and keep working; their SCF box only shows on them. Their read time is now counted from the text too.

## Pagination of the blog (hub and topics)

The hub (`/blog/`) and every topic (`/blog/{topic}/`) show 9 articles a page (`RX_BLOG_PER_PAGE` in `themes/pharmacy/inc/blog.php`).
The list is the page's own main query, so WordPress and Yoast count the pages, canonicals and 404s themselves.

- URLs: `/blog/`, `/blog/page/2/`, `/blog/{topic}/`, `/blog/{topic}/page/2/`. Page 1 never has `/page/1/` (it 301s), and a page past the last gives a real 404.
- Every page is indexable, has its own canonical, a title and description with "Page N of M", real `<a href>` links and `rel="prev"` / `rel="next"` (one of each, Yoast's own are switched off on these views).
- The featured article is shown above the list on the hub's first page only and is left out of the list on every page. The sample post never appears.
- The rewrite rules flush themselves once when `RX_BLOG_RULES_VERSION` changes, so a deploy needs no "Save permalinks".
- Topic and page links are swapped in without a reload (`assets/js/blog-hub.js`); without JavaScript they are plain links.
- Check it from the outside any time: `node wordpress/tools/check-pagination.mjs http://your-site/` (statuses, canonicals, prev/next, unique titles, every article exactly once).
