import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/api/api_client.dart';
import '../../../core/utils/json.dart';

enum ChatRole { user, assistant }

class ChatMessage {
  const ChatMessage(this.role, this.content);
  final ChatRole role;
  final String content;

  Json toJson() => {'role': role.name, 'content': content};
}

class ChatRepository {
  ChatRepository(this._api);
  final ApiClient _api;

  /// The API accepts 1-12 messages, so only the most recent are sent.
  static const maxHistory = 12;

  Future<String> send(List<ChatMessage> history) async {
    final recent = history.length > maxHistory ? history.sublist(history.length - maxHistory) : history;
    final json = await _api.post(
      'chatbot/messages',
      data: {
        'messages': [for (final m in recent) m.toJson()],
      },
    );
    return asString(json['reply']);
  }
}

final chatRepositoryProvider = Provider<ChatRepository>((ref) => ChatRepository(ref.watch(apiClientProvider)));
