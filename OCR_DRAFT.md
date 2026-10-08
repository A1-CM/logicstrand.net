# Scanned PDF OCR: design draft

This is a future phase. The current upload path still accepts text-based PDFs and UTF-8 text, and a scanned PDF with no selectable text still fails with the existing clear error.

## Proposed visitor flow

1. After a scanned PDF is detected, offer “Extract text in this browser.” Do not start OCR without a user action. Explain that it may take several minutes and works best on a desktop device.
2. Render one PDF page at a time in the browser, recognize its text locally, and show progress plus cancel. Keep the original PDF on the user's device until they choose to upload.
3. Show an editable page-by-page preview with page numbers and a warning for pages with little or no recognized text. The user confirms the extracted text before uploading it.
4. Submit the original PDF and the reviewed page text to a dedicated authenticated endpoint. The server validates ownership, plan access, file type and size (the existing 10 MB limit), page count, and text length. It stores the original privately and indexes the reviewed text with each original page number.

## Implementation boundaries

- Use a browser PDF renderer and browser OCR worker. Bundle or self-host the required assets rather than loading a third-party script at runtime. Do not send document pages to an OCR provider.
- Limit concurrent recognition to one page and release rendered page bitmaps promptly to control memory. Provide a clear fallback to a text-based copy when recognition fails or the browser cannot support it.
- Treat browser output as untrusted input. The server must never accept client-supplied owner IDs, paths, or citation IDs. Build chunks and search indexes from validated reviewed text only.
- Keep OCR text separate from the original so the user can inspect the recognized words alongside page references. Define how corrections and reindexing affect answers that cite prior chunks before enabling editing.
- Validate performance and recognition quality on mobile, multi-page scans, rotated pages, mixed text and images, and non-English samples before shipping. Set a page-count cap from those measurements rather than assuming the current 10 MB file cap protects browser memory.

## Release gate

Do not expose an OCR control until the extraction, confirmation, authenticated upload, indexing, citation, failure, and privacy paths are implemented and tested. Existing scanned-PDF behavior remains in effect until then.
