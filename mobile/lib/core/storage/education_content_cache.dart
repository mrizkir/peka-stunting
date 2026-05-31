import 'dart:convert';

import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

class CachedEducationContent {
  const CachedEducationContent({
    required this.menuSlug,
    required this.itemSlug,
    required this.title,
    this.excerpt,
    this.body,
    required this.fetchedAt,
  });

  final String menuSlug;
  final String itemSlug;
  final String title;
  final String? excerpt;
  final String? body;
  final DateTime fetchedAt;
}

class EducationContentCache {
  static const _dbName = 'peka_stunting_cache.db';
  static const _dbVersion = 2;
  static const _contentTable = 'education_content_cache';
  static const _menusTable = 'education_taxonomy_menus_cache';
  static const _menuDetailTable = 'education_taxonomy_menu_detail_cache';
  static const staleAfter = Duration(hours: 24);

  Database? _db;

  Future<Database> _database() async {
    if (_db != null) {
      return _db!;
    }

    final databasesPath = await getDatabasesPath();
    final path = p.join(databasesPath, _dbName);

    _db = await openDatabase(
      path,
      version: _dbVersion,
      onCreate: (db, version) async {
        await db.execute('''
CREATE TABLE $_contentTable (
  menu_slug TEXT NOT NULL,
  item_slug TEXT NOT NULL,
  title TEXT NOT NULL,
  excerpt TEXT,
  body TEXT,
  fetched_at TEXT NOT NULL,
  PRIMARY KEY (menu_slug, item_slug)
)
''');
        await db.execute('''
CREATE TABLE $_menusTable (
  cache_key TEXT PRIMARY KEY,
  payload_json TEXT NOT NULL,
  fetched_at TEXT NOT NULL
)
''');
        await db.execute('''
CREATE TABLE $_menuDetailTable (
  menu_slug TEXT PRIMARY KEY,
  payload_json TEXT NOT NULL,
  fetched_at TEXT NOT NULL
)
''');
      },
      onUpgrade: (db, oldVersion, newVersion) async {
        if (oldVersion < 2) {
          await db.execute('''
CREATE TABLE IF NOT EXISTS $_menusTable (
  cache_key TEXT PRIMARY KEY,
  payload_json TEXT NOT NULL,
  fetched_at TEXT NOT NULL
)
''');
          await db.execute('''
CREATE TABLE IF NOT EXISTS $_menuDetailTable (
  menu_slug TEXT PRIMARY KEY,
  payload_json TEXT NOT NULL,
  fetched_at TEXT NOT NULL
)
''');
        }
      },
    );

    return _db!;
  }

  Future<CachedEducationContent?> get({
    required String menuSlug,
    required String itemSlug,
  }) async {
    final db = await _database();
    final rows = await db.query(
      _contentTable,
      where: 'menu_slug = ? AND item_slug = ?',
      whereArgs: [menuSlug, itemSlug],
      limit: 1,
    );
    if (rows.isEmpty) {
      return null;
    }

    final row = rows.first;
    return CachedEducationContent(
      menuSlug: row['menu_slug'] as String,
      itemSlug: row['item_slug'] as String,
      title: row['title'] as String,
      excerpt: row['excerpt'] as String?,
      body: row['body'] as String?,
      fetchedAt: DateTime.tryParse(row['fetched_at'] as String? ?? '') ??
          DateTime.fromMillisecondsSinceEpoch(0),
    );
  }

  Future<void> upsert({
    required String menuSlug,
    required String itemSlug,
    required String title,
    String? excerpt,
    String? body,
  }) async {
    final db = await _database();
    await db.insert(
      _contentTable,
      {
        'menu_slug': menuSlug,
        'item_slug': itemSlug,
        'title': title,
        'excerpt': excerpt,
        'body': body,
        'fetched_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  bool isStale(CachedEducationContent cached) {
    final age = DateTime.now().difference(cached.fetchedAt);
    return age > staleAfter;
  }

  Future<({List<Map<String, dynamic>> items, DateTime fetchedAt})?> getMenus() async {
    final db = await _database();
    final rows = await db.query(
      _menusTable,
      where: 'cache_key = ?',
      whereArgs: ['target_groups'],
      limit: 1,
    );
    if (rows.isEmpty) {
      return null;
    }

    final row = rows.first;
    final payload = row['payload_json'] as String? ?? '[]';
    final decoded = jsonDecode(payload);
    if (decoded is! List) {
      return null;
    }

    return (
      items: decoded
          .whereType<Map>()
          .map((e) => e.cast<String, dynamic>())
          .toList(),
      fetchedAt: DateTime.tryParse(row['fetched_at'] as String? ?? '') ??
          DateTime.fromMillisecondsSinceEpoch(0),
    );
  }

  Future<void> upsertMenus(List<Map<String, dynamic>> items) async {
    final db = await _database();
    await db.insert(
      _menusTable,
      {
        'cache_key': 'target_groups',
        'payload_json': jsonEncode(items),
        'fetched_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<({Map<String, dynamic> data, DateTime fetchedAt})?> getMenuDetail(
    String menuSlug,
  ) async {
    final db = await _database();
    final rows = await db.query(
      _menuDetailTable,
      where: 'menu_slug = ?',
      whereArgs: [menuSlug],
      limit: 1,
    );
    if (rows.isEmpty) {
      return null;
    }

    final row = rows.first;
    final payload = row['payload_json'] as String? ?? '{}';
    final decoded = jsonDecode(payload);
    if (decoded is! Map) {
      return null;
    }

    return (
      data: decoded.cast<String, dynamic>(),
      fetchedAt: DateTime.tryParse(row['fetched_at'] as String? ?? '') ??
          DateTime.fromMillisecondsSinceEpoch(0),
    );
  }

  Future<void> upsertMenuDetail({
    required String menuSlug,
    required Map<String, dynamic> data,
  }) async {
    final db = await _database();
    await db.insert(
      _menuDetailTable,
      {
        'menu_slug': menuSlug,
        'payload_json': jsonEncode(data),
        'fetched_at': DateTime.now().toIso8601String(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> clearAll() async {
    final db = await _database();
    await db.transaction((txn) async {
      await txn.delete(_contentTable);
      await txn.delete(_menusTable);
      await txn.delete(_menuDetailTable);
    });
  }
}
