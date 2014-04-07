#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>

#define PRE 4
#define POW (PRE * PRE)
#define MAX 2048

static unsigned int board[PRE][PRE];

static void show_board()
{
    size_t i, j;
    for (i = 0; i < PRE; ++i)
    {
        for (j = 0; j < PRE; ++j)
        {
            printf("%u\t", board[i][j]);
        }
        printf("\n");
    }
}

static unsigned int search_left(unsigned int left[POW])
{
    size_t i, j, k = 0;
    for (i = 0; i < PRE; ++i)
    {
        for (j = 0; j < PRE; ++j)
        {
            if (board[i][j] == 0) left[k++] = i * PRE + j;
        }
    }
    return k;
}

static void rand_set(unsigned int left[POW], unsigned int left_size)
{
    unsigned char value = rand() % 2 ? 4 : 2;
    unsigned int where = left[rand() % left_size];
    unsigned int row = where / PRE, col = where % PRE;
    board[row][col] = value;
}

static int can_merge()
{
    unsigned int x, y;
    for (y = 0; y < PRE; ++y)
    {
        for (x = 0; x < PRE; ++x)
        {
            if (x > 0       && board[y][x] == board[y][x - 1]) return 1;
            if (x < PRE - 1 && board[y][x] == board[y][x + 1]) return 1;
            if (y > 0       && board[y][x] == board[y - 1][x]) return 1;
            if (y < PRE - 1 && board[y][x] == board[y + 1][x]) return 1;
        }
    }
    return 0;
}

static int move_left(int* win)
{
    unsigned int x1, y1, x2;
    int done = 0;
    for (y1 = 0; y1 < PRE; ++y1)
    {
        for (x1 = 0; x1 < PRE - 1; ++x1)
        {
            if (board[y1][x1] == 0) // 先做trim操作
            {
                int trim = 0;
                for (x2 = x1 + 1; x2 < PRE; ++x2)
                {
                    if (board[y1][x2])
                    {
                        trim = 1;
                        break;
                    }
                }
                if (trim)
                {
                    done = 1;
                    for (x2 = x1 + 1; x2 < PRE; ++x2) board[y1][x2 - 1] = board[y1][x2];
                    board[y1][PRE - 1] = 0;
                    if (board[y1][x1] == 0) --x1;
                }
            }
        }
        for (x1 = 0; x1 < PRE - 1; ++x1)
        {
            if (board[y1][x1] && board[y1][x1] == board[y1][x1 + 1])
            {
                done = 1;
                board[y1][x1] <<= 1;
                if (board[y1][x1] == MAX) *win = 1;
                for (x2 = x1 + 2; x2 < PRE; ++x2) board[y1][x2 - 1] = board[y1][x2];
                board[y1][PRE - 1] = 0;
            }
        }
    }
    if (!done)
    {
        printf("you can't move left!\n");
        return 0;
    }
    return 1;
}

static int move_right(int* win)
{
    int x1, y1, x2;
    int done = 0;
    for (y1 = 0; y1 < PRE; ++y1)
    {
        for (x1 = PRE - 1; x1 > 0; --x1)
        {
            if (board[y1][x1] == 0) // 先做trim操作
            {
                int trim = 0;
                for (x2 = x1 - 1; x2 >= 0; --x2)
                {
                    if (board[y1][x2])
                    {
                        trim = 1;
                        break;
                    }
                }
                if (trim)
                {
                    done = 1;
                    for (x2 = x1 - 1; x2 >= 0; --x2) board[y1][x2 + 1] = board[y1][x2];
                    board[y1][0] = 0;
                    if (board[y1][x1] == 0) ++x1;
                }
            }
        }
        for (x1 = PRE - 1; x1 > 0; --x1)
        {
            if (board[y1][x1] && board[y1][x1] == board[y1][x1 - 1])
            {
                done = 1;
                board[y1][x1] <<= 1;
                if (board[y1][x1] == MAX) *win = 1;
                for (x2 = x1 - 2; x2 >= 0; --x2) board[y1][x2 + 1] = board[y1][x2];
                board[y1][0] = 0;
            }
        }
    }
    if (!done)
    {
        printf("you can't move right!\n");
        return 0;
    }
    return 1;
}

static int move_up(int* win)
{
    unsigned int x1, y1, y2;
    int done = 0;
    for (x1 = 0; x1 < PRE; ++x1)
    {
        for (y1 = 0; y1 < PRE - 1; ++y1)
        {
            if (board[y1][x1] == 0) // 先做trim操作
            {
                int trim = 0;
                for (y2 = y1 + 1; y2 < PRE; ++y2)
                {
                    if (board[y2][x1])
                    {
                        trim = 1;
                        break;
                    }
                }
                if (trim)
                {
                    done = 1;
                    for (y2 = y1 + 1; y2 < PRE; ++y2) board[y2 - 1][x1] = board[y2][x1];
                    board[PRE - 1][x1] = 0;
                    if (board[y1][x1] == 0) --y1;
                }
            }
        }
        for (y1 = 0; y1 < PRE - 1; ++y1)
        {
            if (board[y1][x1] && board[y1][x1] == board[y1 + 1][x1])
            {
                done = 1;
                board[y1][x1] <<= 1;
                if (board[y1][x1] == MAX) *win = 1;
                for (y2 = y1 + 2; y2 < PRE; ++y2) board[y2 - 1][x1] = board[y2][x1];
                board[PRE - 1][x1] = 0;
            }
        }
    }
    if (!done)
    {
        printf("you can't move up!\n");
        return 0;
    }
    return 1;
}

static int move_down(int* win)
{
    int x1, y1, y2;
    int done = 0;
    for (x1 = 0; x1 < PRE; ++x1)
    {
        for (y1 = PRE - 1; y1 > 0; --y1)
        {
            if (board[y1][x1] == 0) // 先做trim操作
            {
                int trim = 0;
                for (y2 = y1 - 1; y2 >= 0; --y2)
                {
                    if (board[y2][x1])
                    {
                        trim = 1;
                        break;
                    }
                }
                if (trim)
                {
                    done = 1;
                    for (y2 = y1 - 1; y2 >= 0; --y2) board[y2 + 1][x1] = board[y2][x1];
                    board[0][x1] = 0;
                    if (board[y1][x1] == 0) ++y1;
                }
            }
        }
        for (y1 = PRE - 1; y1 > 0; --y1)
        {
            if (board[y1][x1] && board[y1][x1] == board[y1 - 1][x1])
            {
                done = 1;
                board[y1][x1] <<= 1;
                if (board[y1][x1] == MAX) *win = 1;
                for (y2 = y1 - 2; y2 >= 0; --y2) board[y2 + 1][x1] = board[y2][x1];
                board[0][x1] = 0;
            }
        }
    }
    if (!done)
    {
        printf("you can't move down!\n");
        return 0;
    }
    return 1;
}

static void new_game()
{
    int win = 0;
    unsigned int left[POW];
    unsigned int left_size;
    char input;
    int rc;
    memset(board, 0, sizeof(board));

    left_size = search_left(left);
    rand_set(left, left_size);
    left_size = search_left(left);
    rand_set(left, left_size);
    //board[0][0] = 4;
    //board[1][0] = 4;
    show_board();

    while (!win)
    {
        input = getc(stdin);
        switch (input)
        {
        case 'a':
        case 'A':
            rc = move_left(&win);
            break;
        case 'd':
        case 'D':
            rc = move_right(&win);
            break;
        case 'w':
        case 'W':
            rc = move_up(&win);
            break;
        case 's':
        case 'S':
            rc = move_down(&win);
            break;
        }
        if (rc)
        {
            left_size = search_left(left);
            rand_set(left, left_size);
            show_board();
            left_size = search_left(left);
            if (!can_merge() && left_size == 0) break;
        }
    }
    if (win) printf("you win!!!!!!!!!!\n");
    else printf("you lose!!!!!!!!!!\n");
}

int main()
{
    printf("press 'w', 's', 'a', 'd' to move number.\n");
    srand(time(NULL));
    while (1)
    {
        new_game();
    }
    return 0;
}

