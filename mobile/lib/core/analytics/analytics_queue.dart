import 'dart:convert';

import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

class PendingAnalyticsEvent {
  const PendingAnalyticsEvent({
    required this.id,
    required this.eventName,
    required this.sessionId,
    required this.occurredAt,
    this.propertiesJson,
  });

  final int id;
  final String eventName;
  final String sessionId;
  final DateTime occurredAt;
  final String? propertiesJson;

  Map<String, dynamic> toPayload() {
    final properties = propertiesJson == null || propertiesJson!.isEmpty
        ? null
        : jsonDecode(propertiesJson!) as Map<String, dynamic>;

    return {
      'event_name': eventName,
      'session_id': sessionId,
      'occurred_at': occurredAt.toUtc().toIso8601String(),
      if (properties != null) 'properties': properties,
    };
  }
}

class PendingUsageSession {
  const PendingUsageSession({
    required this.id,
    required this.sessionId,
    required this.startedAt,
    required this.endedAt,
    required this.durationSeconds,
  });

  final int id;
  final String sessionId;
  final DateTime startedAt;
  final DateTime endedAt;
  final int durationSeconds;

  Map<String, dynamic> toPayload() {
    return {
      'session_id': sessionId,
      'started_at': startedAt.toUtc().toIso8601String(),
      'ended_at': endedAt.toUtc().toIso8601String(),
      'duration_seconds': durationSeconds,
    };
  }
}

class AnalyticsQueue {
  static const _dbName = 'peka_stunting_analytics.db';
  static const _dbVersion = 1;
  static const _eventsTable = 'pending_events';
  static const _sessionsTable = 'pending_sessions';

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
CREATE TABLE $_eventsTable (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  event_name TEXT NOT NULL,
  session_id TEXT NOT NULL,
  occurred_at TEXT NOT NULL,
  properties_json TEXT
)
''');
        await db.execute('''
CREATE TABLE $_sessionsTable (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  session_id TEXT NOT NULL,
  started_at TEXT NOT NULL,
  ended_at TEXT NOT NULL,
  duration_seconds INTEGER NOT NULL
)
''');
      },
    );

    return _db!;
  }

  Future<void> enqueueEvent({
    required String eventName,
    required String sessionId,
    required DateTime occurredAt,
    Map<String, String>? properties,
  }) async {
    final db = await _database();
    await db.insert(_eventsTable, {
      'event_name': eventName,
      'session_id': sessionId,
      'occurred_at': occurredAt.toUtc().toIso8601String(),
      'properties_json':
          properties == null ? null : jsonEncode(properties),
    });
  }

  Future<void> enqueueSession({
    required String sessionId,
    required DateTime startedAt,
    required DateTime endedAt,
    required int durationSeconds,
  }) async {
    final db = await _database();
    await db.insert(_sessionsTable, {
      'session_id': sessionId,
      'started_at': startedAt.toUtc().toIso8601String(),
      'ended_at': endedAt.toUtc().toIso8601String(),
      'duration_seconds': durationSeconds,
    });
  }

  Future<List<PendingAnalyticsEvent>> peekEvents({int limit = 20}) async {
    final db = await _database();
    final rows = await db.query(
      _eventsTable,
      orderBy: 'id ASC',
      limit: limit,
    );

    return rows
        .map(
          (row) => PendingAnalyticsEvent(
            id: row['id']! as int,
            eventName: row['event_name']! as String,
            sessionId: row['session_id']! as String,
            occurredAt: DateTime.parse(row['occurred_at']! as String),
            propertiesJson: row['properties_json'] as String?,
          ),
        )
        .toList();
  }

  Future<List<PendingUsageSession>> peekSessions({int limit = 10}) async {
    final db = await _database();
    final rows = await db.query(
      _sessionsTable,
      orderBy: 'id ASC',
      limit: limit,
    );

    return rows
        .map(
          (row) => PendingUsageSession(
            id: row['id']! as int,
            sessionId: row['session_id']! as String,
            startedAt: DateTime.parse(row['started_at']! as String),
            endedAt: DateTime.parse(row['ended_at']! as String),
            durationSeconds: row['duration_seconds']! as int,
          ),
        )
        .toList();
  }

  Future<void> deleteEvents(Iterable<int> ids) async {
    if (ids.isEmpty) {
      return;
    }

    final db = await _database();
    final placeholders = List.filled(ids.length, '?').join(',');
    await db.delete(
      _eventsTable,
      where: 'id IN ($placeholders)',
      whereArgs: ids.toList(),
    );
  }

  Future<void> deleteSessions(Iterable<int> ids) async {
    if (ids.isEmpty) {
      return;
    }

    final db = await _database();
    final placeholders = List.filled(ids.length, '?').join(',');
    await db.delete(
      _sessionsTable,
      where: 'id IN ($placeholders)',
      whereArgs: ids.toList(),
    );
  }
}
