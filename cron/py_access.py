#Edit line 8 as needed
import MySQLdb as mdb
_connection = None

def get_connection():
    global _connection
    if not _connection:
        _connection = mdb.connect(host="localhost", user = "root", passwd = "passw0rd", db = "pihome")
    return _connection

# List of stuff accessible to importers of this module. Just in case
__all__ = [ 'getConnection' ]
