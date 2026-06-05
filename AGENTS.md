# OpenCode rules

Do not use subagents.
Do not use the task tool.
Do not delegate tasks.

Use only these tools when needed:
- bash
- read
- grep
- glob
- write
- edit

Never output XML-like tool calls such as:
<function=...>
<tool_call>
</tool_call>

When you need to inspect the project, use bash commands directly:
- pwd
- ls -la
- find . -maxdepth 2 -type f
