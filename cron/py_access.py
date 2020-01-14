#Edit line 7 as needed
_connection = None

def get_connection():
    global _connection
    if not _connection:
        _connection = MySQLdb.connect(host="localhost", user = "root", password = "passw0rd", dbname = "pihome")
    return _connection

# List of stuff accessible to importers of this module. Just in case
__all__ = [ 'getConnection' ]
