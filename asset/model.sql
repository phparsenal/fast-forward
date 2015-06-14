
-- Table: bookmark_type
CREATE TABLE bookmark_type ( 
    id        INTEGER PRIMARY KEY AUTOINCREMENT
                      NOT NULL,
    name      VARCHAR NOT NULL,
    namespace VARCHAR NOT NULL 
);


-- Table: bookmark
CREATE TABLE bookmark ( 
    id               INTEGER PRIMARY KEY AUTOINCREMENT
                             NOT NULL,
    shortcut         VARCHAR NOT NULL,
    description      VARCHAR NOT NULL,
    command          VARCHAR NOT NULL,
    hit_count        INTEGER NOT NULL,
    ts_created       INTEGER NOT NULL,
    bookmark_type_id INTEGER NOT NULL
                             REFERENCES bookmark_type ( id ) 
);

