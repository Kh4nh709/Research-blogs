# XSS

# XSS (Cross-Site Scripting)

---

Chủng lỗi trên phía client site cho phép attacker chèn “injection” thêm nội dung HTML tab vào browser của victim nhằm thực thi code JS. Với mục tiêu thực thi trái phép trên browser của victim

# Rootcause của XSS

---

Nguyên nhân xảy ra lỗi XSS là browser thực thi cả các nội dung tưởng nhầm nó là HTML tab or JS trên browser. Có thể qua nhiều lý do:

1. Tin tưởng vào nội dung truyền vào
2. Ko escaping lại nội dung 
3. API DOM ko an toàn
4. …

# XSS xảy ra như nào

---

```
[HTML injection] -> [JS execute] -> [interac DOM] -> [XSS]
```

Cốt lỗi của XSS vẫn là thực thi dc JS và tương tác DOM tree.

# Cách thực thi JS trên HTML

---

Trong 1 page HTML có 8 cách viết HTML

1. Inline script
2. External JS file
3. Event Attribute
4. Gán JS từ File bên ngoài, Or thẻ script bằng DOM API
5. Nhúng JS vào Thuộc tính URL
6. Sử dụng HTML event listener
7. Module Script
8. Nhúng JS trong HTML data

Nhưng trong XSS thường chỉ có 3 kỹ thuật:

1. Inline script
2. Event Attribute
3. Nhúng JS vào Thuộc tính URL

## Inline script

---

kiểu này là đặt script vào trong line của HTML. Ta sử dụng element script đặt vào code HTML. VÍ dụ:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Hello World</h1>
    <script>alert('xss')</script>
</body>
</html>
```

## Event Attribute

Thực thi JS thông qua các Event attribute của element. không cần thông qua tab script. Ví dụ

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Hello World</h1>
    <img src="/test" onerror="alert('xss')">
</body>
</html>
```

## Nhúng JS vào URL

cách này ta sẽ thực thi JS bằng protocol `javascript:` bằng cách này ta sẽ nhúng JS vào attribute gọi link như `href=””` 

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="javascript:alert('xss')">Click me (anchor)</a>
</body>
</html>
```

# DOM TREE

---

## DOM là gì

---

Dom “Direct Object Model” là một dạng cấu trúc hình cây bên trong nó các Element, Attribute đều là node thuộc DOM. ví dụ:

```html
<!DOCTYPE html>
<html>
  <head>
    <title>Trang của tôi</title>
  </head>
  <body>
    <h1>Chào bạn!</h1>
    <p>Đây là DOM.</p>
  </body>
</html>
```

```html
📄 Document
   └── <html> (Nút gốc - Root)
        ├── <head> (Con của <html>)
        │    └── <title> (Con của <head>)
        │         └── "Trang của tôi" (Nút văn bản)
        └── <body> (Con của <html>)
             ├── <h1> (Con của <body>)
             │    └── "Chào bạn!" (Nút văn bản)
             └── <p> (Con của <body>)
                  └── "Đây là DOM." (Nút văn bản)
```

## Cách truy cập DOM

---

Để truy cập DOM ta sử dụng JS làm phương thức truy cập. Có 2 cách phổ biến truy cập DOM.

1. getElementByID()
    
    Cách này lọc ra các phần tử thuộc DOM dựa trên Attribute ID của các Element. Ví dụ: `document.getElementByID(’ele_1’)`
    
2. querySelector()
    
    Dùng bộ chọn CSS để lọc. Ví dụ:
    
    - `document.querySelector(".ten-cua-class")` (tìm bằng class)
    - `document.querySelector("#ten-cua-id")` (tìm bằng ID)
    - `document.querySelector("p")` (tìm bằng tên thẻ)

## Thao tác với DOM

---

Thao tác DOM là hoạt động thay đỗi DOM sau khi trang đã tải xong. Khi thay đổi DOM trình duyệt sẽ tự cập nhật.

Có 3 thao tác chính:

1. Thay đổi nội dung “Context”
2. Thay đổi thuộc tính “Attribute”
3. Thêm Hoạc xóa Element

### Thay đổi context

---

Có 2 hướng thay đổi nội dung:

1. `textContent`: Thay đổi nội dung theo hướng an toàn. sẽ hiển thị nội dung như văn bản dù là TAB HTML
2. innerText: Thay đổi nội dung HTML bên trong. Hướng này sẽ thay đổi nội dung sẽ đc render ra. Kể cả các TAB HTML.

Ví dụ:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p id="DOM_1">Hello World</p>
    <script>
        document.getElementById("DOM_1").textContent = "<h1> Hello JavaScript</h1>";
    </script>
</body>
</html>
```

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <p id="DOM_1">Hello World</p>
    <script>
        document.getElementById("DOM_1").innerText = "<h1> Hello JavaScript </h1>";
    </script>
</body>
</html>
```

### Thay đổi Attribute

---

Cho phép thay đổi attribute của một Element:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <img id="DOM_1" src="/test">This im images</p>
    <script>
        let img = document.getElementById("DOM_1")
        img.src = '/changed'
        img.onerror= function(){
            alert('xss');
        }
    </script>
</body>
</html>
```

### Thêm và xóa phần tử “Element”

---

Có thể Thêm or xóa các Element trong cây DOM. ví dụ:

- **`ocument.createElement(tenThe)`**: Tạo ra một phần tử (node) HTML mới (ví dụ: `document.createElement("p")` tạo ra thẻ `<p>`).
- **`element.appendChild(phanTuMoi)`**: Gắn `phanTuMoi` làm *con cuối cùng* của `element`.
- **`element.remove()`**: Xóa `element` ra khỏi DOM.

```
// Giả sử chúng ta có HTML: <div id="container"></div>
//                           <h1 id="tieu-de-cu">Xóa tôi đi!</h1>

// --- Thêm phần tử ---
// 1. Bắt lấy "cha"
let container = document.getElementById("container");

// 2. Tạo phần tử mới
let newParagraph = document.createElement("p");
newParagraph.textContent = "Tôi là đoạn văn mới!"; // Thêm nội dung cho nó

// 3. Gắn phần tử mới vào "cha"
container.appendChild(newParagraph);
// Kết quả HTML: <div id="container"><p>Tôi là đoạn văn mới!</p></div>

// --- Xóa phần tử ---
// 1. Bắt lấy phần tử muốn xóa
let oldHeading = document.getElementById("tieu-de-cu");

// 2. Xóa nó
oldHeading.remove();
// Thẻ h1 đã biến mất khỏi trang
```

# Attribute thực thi JS

## Thuộc tính Xử lý Sự kiện (Event Handlers)

---

### Sự kiện Chuột (Mouse Events)

Kích hoạt khi người dùng tương tác bằng chuột.

- **`onclick`**: Nhấp chuột trái.
- **`ondblclick`**: Nhấp đúp chuột trái.
- **`onmousedown`**: Nhấn nút chuột xuống.
- **`onmouseup`**: Thả nút chuột ra.
- **`onmousemove`**: Di chuyển con trỏ chuột trên một phần tử.
- **`onmouseover`**: Di chuyển con trỏ chuột *vào* một phần tử.
- **`onmouseout`**: Di chuyển con trỏ chuột *ra khỏi* một phần tử.
- **`oncontextmenu`**: Nhấp chuột phải (mở menu ngữ cảnh).
- **`onwheel`**: Lăn bánh xe chuột.

### Sự kiện Bàn phím (Keyboard Events)

Kích hoạt khi người dùng tương tác bằng bàn phím.

- **`onkeydown`**: Nhấn một phím xuống.
- **`onkeyup`**: Thả một phím ra.
- **`onkeypress`**: (Cũ hơn, nhưng vẫn tồn tại) Nhấn và giữ một phím (thường là phím in được ký tự).

### Sự kiện Biểu mẫu (Form Events)

Liên quan đến việc tương tác với các phần tử trong biểu mẫu (`<form>`, `<input>`, `<textarea>`, `<select>`).

- **`onsubmit`**: Gửi một biểu mẫu (thường nhấn nút "Submit").
- **`onreset`**: Đặt lại (reset) một biểu mẫu.
- **`onchange`**: Giá trị của một phần tử (như `<select>`, `<input type="checkbox">`, `<input type="file">`) đã thay đổi *và* mất focus.
- **`oninput`**: Giá trị của `<input>` hoặc `<textarea>` thay đổi (kích hoạt ngay lập tức mỗi khi gõ phím, dán, v.v.).
- **`onfocus`**: Một phần tử nhận được focus (ví dụ: nhấp vào một ô input).
- **`onblur`**: Một phần tử mất focus.
- **`onselect`**: Người dùng chọn (bôi đen) văn bản bên trong một phần tử.
- **`oninvalid`**: Dữ liệu nhập vào không vượt qua được xác thực (validation) của HTML5.

### Sự kiện Tải/Lỗi (Resource Events)

Liên quan đến việc tải các tài nguyên của trang.

- **`onload`**:
    - Trên `<body>`: Toàn bộ trang (bao gồm hình ảnh, script) đã tải xong.
    - Trên `<img>`, `<script>`: Tài nguyên cụ thể đó đã tải xong.
- **`onerror`**: Xảy ra lỗi khi tải một tài nguyên (ví dụ: đường dẫn ảnh bị sai). Đây là một vector XSS rất phổ biến.
- **`onunload`**: Người dùng đang rời khỏi trang (đóng tab, điều hướng đi nơi khác).
- **`onbeforeunload`**: Kích hoạt ngay trước khi người dùng rời khỏi trang (cho phép hiển thị thông báo "Bạn có chắc muốn rời đi?").

### Sự kiện Media (Audio/Video Events)

Sử dụng cho các thẻ `<audio>` và `<video>`.

- **`onplay`**: Bắt đầu phát.
- **`onpause`**: Tạm dừng.
- **`onended`**: Phát xong đến cuối.
- **`ontimeupdate`**: Thời gian phát hiện tại thay đổi (kích hoạt liên tục khi đang phát).
- **`onvolumechange`**: Âm lượng thay đổi.
- **`onseeking`**: Người dùng bắt đầu tua.
- **`onseeked`**: Người dùng tua xong.

### Sự kiện Clipboard (Sao chép/Dán)

Kích hoạt khi người dùng thực hiện các hành động sao chép, cắt, dán.

- **`oncopy`**: Sao chép nội dung.
- **`oncut`**: Cắt nội dung.
- **`onpaste`**: Dán nội dung.

### Sự kiện Kéo & Thả (Drag & Drop)

Liên quan đến API Kéo và Thả của HTML5.

- **`ondragstart`**: Bắt đầu kéo một phần tử.
- **`ondrag`**: Đang trong quá trình kéo.
- **`ondragend`**: Kết thúc kéo (thả chuột).
- **`ondragenter`**: Kéo một phần tử *vào* một vùng thả hợp lệ.
- **`ondragleave`**: Kéo một phần tử *ra khỏi* một vùng thả.
- **`ondragover`**: Kéo một phần tử *bên trên* một vùng thả (cần `preventDefault` để cho phép `ondrop`).
- **`ondrop`**: Thả một phần tử vào một vùng thả hợp lệ.

### Sự kiện Khác (Miscellaneous Events)

- **`onscroll`**: Cuộn nội dung của một phần tử (hoặc toàn bộ trang).
- **`ontoggle`**: Mở hoặc đóng thẻ `<details>`.
- **`onresize`**: Kích thước cửa sổ trình duyệt thay đổi (thường dùng trên `<body>`).

## Thuộc tính Dựa trên URL (URL-based)

---

- **`href`**:
    - **Thẻ:** `<a>`, `<area>`, `<base>`
    - **Mục đích:** Chỉ định URL cho một liên kết.
    - **Ví dụ khai thác:** `<a href="javascript:alert('XSS từ href')">Click me</a>`
- **`src`**:
    - **Thẻ:** `<iframe>`, `<frame>`, `<embed>`, `<script>`, `<img>`, `<audio>`, `<video>`, `<input type="image">`
    - **Mục đích:** Chỉ định nguồn (source) của một tài nguyên bên ngoài.
    - **Ví dụ khai thác (iframe/embed):** `<iframe src="javascript:alert('XSS từ src')"></iframe>`
    - **Lưu ý:** Hầu hết các trình duyệt hiện đại đã chặn `javascript:` trong các thẻ như `<img>`, `<script>`, `<audio>`, và `<video>` vì lý do bảo mật. Tuy nhiên, `<iframe>` và `<embed>` vẫn là các vector phổ biến.
- **`action`**:
    - **Thẻ:** `<form>`
    - **Mục đích:** Chỉ định URL sẽ xử lý dữ liệu biểu mẫu khi được gửi đi.
    - **Ví dụ khai thác:** `<form action="javascript:alert('XSS từ action')"><button type="submit">Submit</button></form>`
- **`formaction`**:
    - **Thẻ:** `<button>`, `<input type="submit">`
    - **Mục đích:** Ghi đè thuộc tính `action` của biểu mẫu cha cho một nút cụ thể.
    - **Ví dụ khai thác:** `<form><button formaction="javascript:alert('XSS từ formaction')">Submit to JS</button></form>`
- **`data`**:
    - **Thẻ:** `<object>`
    - **Mục đích:** Chỉ định URL của một tài nguyên sẽ được nhúng.
    - **Ví dụ khai thác:** `<object data="javascript:alert('XSS từ data')"></object>`
- **`background`** (Lỗi thời):
    - **Thẻ:** `<body>`, `<table>`, `<td>`, `<th>`
    - **Mục đích:** Chỉ định một hình nền.
    - **Ví dụ khai thác:** `<body background="javascript:alert('XSS từ background')">`
- **`cite`** (Ít phổ biến):
    - **Thẻ:** `<blockquote>`, `<q>`, `<del>`, `<ins>`
    - **Mục đích:** Chỉ định URL nguồn cho một trích dẫn hoặc thay đổi.
    - **Ví dụ khai thác:** `<blockquote cite="javascript:alert('XSS từ cite')">Trích dẫn...</blockquote>` (Mặc dù hầu hết các trình duyệt hiện đại không thực thi JS từ thuộc tính này, nó vẫn đáng được lưu ý).
- **`style`** (Trường hợp đặc biệt):
    - **Thẻ:** Bất kỳ thẻ nào.
    - **Mục đích:** Khai báo CSS nội tuyến. Nó không trực tiếp lấy URL, nhưng hàm `url()` bên trong CSS có thể.
    - **Ví dụ khai thác:** `<div style="background-image: url('javascript:alert(1)')"></div>` (Bị chặn bởi hầu hết các trình duyệt hiện đại, nhưng các phiên bản cũ hơn hoặc các hàm CSS ít người biết đến có thể là vector).

# Phân loại XSS

XSS được phân thành 3 loại chính:

1. Stored XSS
2. Reflected XSS
3. DOM-Base XSS

## Stored XSS

---

Loại lỗi này xảy ra khi payload được lưu ở phía máy chủ và lúc nào cũng có thể trả về phía client site

Ví dụ:

```
<img src="test" onerror="alert('xss')">
```

được lưu trên database và sẽ gọi ra khi truy cập

## Reflected XSS

---

Loại lỗi này xảy ra khi payload được gửi về server và server ko lưu nó mà phản hồi về phía client gây XSS.

Ví dụ:

```
/profile.php?name=<img+src="test"+onerror="alert('xss')">
```

## DOM-Base XSS

---

xảy ra hoàn toàn phía Client site trong code JS của trang. Client tự lấy dữ liệu từ URL và đưa nó vào DOM.

Ví dụ:

```
/profile.php#admin
```

Client lấy `name=’admin’` đưa vào DOM:

```html
<h1>hello admin</h1>
```

lúc này ta có payload:

```
/profile.php#<img+src=""+onerror="alert('xss')">
```

```html
<h1>hello <img+src=""+onerror="alert('xss')"></h1>
```

# Chặn XSS

- **Mã hóa (Encoding) dữ liệu đầu ra**: Cách làm cho trình duyệt hiểu dữ liệu là *văn bản* chứ không phải *mã* để thực thi.
- **Xác thực (Validation) dữ liệu đầu vào**: Cách từ chối dữ liệu không hợp lệ ngay từ đầu.
- **Chính sách Bảo mật Nội dung (Content Security Policy - CSP)**: Một lớp bảo vệ hiện đại ở cấp trình duyệt.