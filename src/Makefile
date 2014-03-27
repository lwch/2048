CC=gcc
CFLAGS=-Wall -O3 -s -DNDEBUG
LDFLAGS=
TARGET=2048

TARGET_OBJS = main.o

all: link

link: $(TARGET_OBJS)
	$(CC) $(CFLAGS) $(LDFLAGS) $(TARGET_OBJS) -o $(TARGET)

main.o: main.c
	$(CC) $(CFLAGS) -c $< -o $@

clean:
	rm -fr $(TARGET_OBJS) $(TARGET)

