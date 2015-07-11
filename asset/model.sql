CREATE TABLE "bookmark" (
  "id"          INTEGER PRIMARY KEY AUTOINCREMENT
                        NOT NULL,
  "shortcut"    VARCHAR NOT NULL,
  "description" VARCHAR NOT NULL,
  "command"     VARCHAR NOT NULL,
  "hit_count"   INTEGER NOT NULL,
  "ts_created"  INTEGER NOT NULL,
  'ts_modified' INTEGER NOT NULL
);

CREATE TABLE "setting" (
  "key" text NOT NULL,
  "value" text NOT NULL
);
