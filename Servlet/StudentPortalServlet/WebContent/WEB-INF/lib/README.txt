=====================================================================
 WEB-INF/lib  -  JDBC driver folder
=====================================================================
Copy the MySQL JDBC driver JAR into THIS folder before running:

    mysql-connector-j-8.x.x.jar

Download (free, official): https://dev.mysql.com/downloads/connector/j/
  -> choose "Platform Independent" -> extract the ZIP -> copy the .jar here.

Tomcat automatically adds every JAR in WEB-INF/lib to the classpath of
this web application, so DBConnection.java can load
"com.mysql.cj.jdbc.Driver".

Note: servlet-api.jar must NOT be copied here - Tomcat already provides it.
