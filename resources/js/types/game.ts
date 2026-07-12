export interface GamePlayer {
    id: number;
    name: string;
    score: number;
}

export interface BoardClueCell {
    gameClueId: number;
    value: number;
    status: 'hidden' | 'open' | 'answered';
}

export interface BoardCategory {
    id: number;
    name: string;
    clues: BoardClueCell[];
}

export interface OpenClue {
    gameClueId: number;
    category: string;
    value: number;
    prompt: string;
    buzzedPlayer: { id: number; name: string } | null;
    lockedOutPlayerIds: number[];
}

export interface GameState {
    code: string;
    status: 'lobby' | 'active' | 'finished';
    boardName: string;
    players: GamePlayer[];
    controllingPlayer: { id: number; name: string } | null;
    categories: BoardCategory[];
    openClue: OpenClue | null;
}

export interface GameStateEvent {
    state: GameState;
}

export interface AnswerJudgedEvent extends GameStateEvent {
    correct: boolean;
    playerName: string;
}

export interface HostClueDetail {
    prompt: string;
    correctResponse: string;
    category: string;
    value: number;
}
