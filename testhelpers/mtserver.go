package main

import (
	"fmt"
	"github.com/weibocom/motan-go"
	m "github.com/weibocom/motan-go/core"
	"time"
)

func main() {
	msctc := motan.GetMotanServerContext("./motan.yaml")
	msctc.RegisterService(&MTService{}, "")
	msctc.Start(nil)
	msctc.ServicesAvailable()
	time.Sleep(time.Minute * 99999999)
}

// MTService for type test Server
type MTService struct{}

// Hello test func for string map
func (mt *MTService) Hello(args map[string]string) string {
	b := m.NewBytesBuffer(1000)
	x := b.Bytes()
	b.WriteZigzag64(64)
	b.WriteZigzag64(1)
	b.WriteZigzag32(64)
	b.WriteZigzag32(1)
	f := b.Bytes()
	return fmt.Sprintf("%+v-------%+v", x, f)
}

// HelloW test func for string map
func (mt *MTService) HelloW(args map[string]string) string {
	return fmt.Sprintf("%+v", args)
}

func pow(x, n int64) (ret int64) {
	ret = 1
	for n != 0 {
		if n%2 != 0 {
			ret = ret * x
		}
		n /= 2
		x = x * x
	}
	return ret
}

// HelloX test func for multi args
func (mt *MTService) HelloX(strArg string, inT64 int64, inT32 int64, nameArr []string) string {
	return fmt.Sprintf("strArg:%s-inT64:%d-int32:%d-%+v", strArg, inT64, inT32, nameArr)
}

// HelloY test func for multi args
func (mt *MTService) HelloY(strArg string, nameArr []string) string {
	return fmt.Sprintf("strArg:%s-%+v", strArg, nameArr)
}

// HelloZ test func for multi args
func (mt *MTService) HelloZ(strArg string, inT64 int64, inT32 int64, nameArr []string) int64 {
	return 92233720
	// return 9223372036854775808
}

// HelloZ32 test func for multi args
func (mt *MTService) HelloZ32(strArg string, inT64 int64, inT32 int64, nameArr []string) int32 {
	return 2222222
	// return 9223372036854775808
}
