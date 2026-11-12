import '../../../core/utils/json.dart';

enum TicketStatus {
  open,
  answered,
  closed,
  unknown;

  static TicketStatus parse(Object? value) => switch (value) {
    'open' => TicketStatus.open,
    'answered' => TicketStatus.answered,
    'closed' => TicketStatus.closed,
    _ => TicketStatus.unknown,
  };
}

class TicketSummary {
  const TicketSummary({
    required this.id,
    required this.subject,
    required this.status,
    required this.statusLabel,
    required this.isClosed,
    this.createdAt,
    this.lastActivityAt,
  });

  final int id;
  final String subject;
  final TicketStatus status;
  final String statusLabel;
  final bool isClosed;
  final DateTime? createdAt;
  final DateTime? lastActivityAt;

  factory TicketSummary.fromJson(Json json) {
    final status = TicketStatus.parse(json['status']);
    return TicketSummary(
      id: asInt(json['id']),
      subject: asString(json['subject']),
      status: status,
      statusLabel: asStringOrNull(json['status_label']) ?? asString(json['status'], 'Unknown'),
      isClosed: asBool(json['is_closed'], status == TicketStatus.closed),
      createdAt: asDateTime(json['created_at']),
      lastActivityAt: asDateTime(json['last_activity_at']),
    );
  }
}

class TicketAttachment {
  const TicketAttachment({required this.index, required this.name, this.downloadUrl});

  final int index;
  final String name;
  final String? downloadUrl;

  factory TicketAttachment.fromJson(Json json) => TicketAttachment(
    index: asInt(json['index']),
    name: asString(json['name'], 'Attachment'),
    downloadUrl: asStringOrNull(json['download_url']),
  );
}

class TicketReply {
  const TicketReply({
    required this.id,
    required this.body,
    required this.fromStaff,
    required this.authorName,
    this.createdAt,
    this.attachments = const [],
  });

  final int id;
  final String body;
  final bool fromStaff;
  final String authorName;
  final DateTime? createdAt;
  final List<TicketAttachment> attachments;

  factory TicketReply.fromJson(Json json) => TicketReply(
    id: asInt(json['id']),
    body: asString(json['body']),
    fromStaff: asBool(json['from_staff']),
    authorName: asString(json['author_name']),
    createdAt: asDateTime(json['created_at']),
    attachments: asList(json['attachments'], TicketAttachment.fromJson),
  );
}

class TicketDetail extends TicketSummary {
  const TicketDetail({
    required super.id,
    required super.subject,
    required super.status,
    required super.statusLabel,
    required super.isClosed,
    super.createdAt,
    super.lastActivityAt,
    this.replies = const [],
  });

  final List<TicketReply> replies;

  factory TicketDetail.fromJson(Json json) {
    final base = TicketSummary.fromJson(json);
    return TicketDetail(
      id: base.id,
      subject: base.subject,
      status: base.status,
      statusLabel: base.statusLabel,
      isClosed: base.isClosed,
      createdAt: base.createdAt,
      lastActivityAt: base.lastActivityAt,
      replies: asList(json['replies'], TicketReply.fromJson),
    );
  }
}
