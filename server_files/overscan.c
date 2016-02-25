#include <stdio.h>
#include <string.h>
#include <fcntl.h>
#include <stdint.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/ioctl.h>

static int mailbox_property(int file_desc, void *buf)
{
    int return_value = ioctl(file_desc, _IOWR(100, 0, char *), buf);
    
    /* ioctl error of some kind */
    if (return_value < 0) {
        close(file_desc);
        exit(2);
    }
    
    return return_value;
}

static unsigned set_overscan(int file_desc, unsigned coord[4])
{
    int i=0;
    unsigned property[32];
    property[i++] = 0;
    property[i++] = 0x00000000;

    property[i++] = 0x0004800a;
    property[i++] = 0x00000010;
    property[i++] = 0x00000010;
    property[i++] = coord[0]; /* top */
    property[i++] = coord[1]; /* bottom */
    property[i++] = coord[2]; /* left */
    property[i++] = coord[3]; /* right */
    property[i++] = 0x00000000;
    property[0] = i*sizeof *property;

    mailbox_property(file_desc, property);
    coord[0] = property[5]; /* top */
    coord[1] = property[6]; /* bottom */
    coord[2] = property[7]; /* left */
    coord[3] = property[8]; /* right */
    return 0;
}

int main(int argc, char *argv[])
{
    int file_desc;
    
    /* Create array to hold overscan coords */
    unsigned overscans[4];
    
    /* Make sure the device is ready */
    file_desc = open("/dev/vcio", 0);
    if (file_desc == -1)
        exit(1);
    
    /* Load passed args into overscans */
    if (argc == 5) {
        for (int i=0; i<4; i++)
            if (argc > 1+i)
                overscans[i] = strtoul(argv[1+i], 0, 0);
        
    /* Activate overscan values */
    set_overscan(file_desc, overscans);
    close(file_desc);
    return 0;
    }
}